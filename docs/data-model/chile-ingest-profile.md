# Chile pepper ingest — profiling notes and carryover

**Status:** Sources located, pulled, and profiled. No transformation built.
**Date:** 2026-09-14
**Destination:** folds into `cash-rents-data-model.md` §9 at v0.4.0, replacing the
forward-design text written before the sources were inspected.

---

## Before anything else

The six Quick Stats CSVs were pulled into a chat session and are **not yet in
the project or the repo.** They must be saved somewhere durable or they will
have to be re-pulled.

- [ ] Save the five distinct CSVs to the same folder as the cash rents extract.
- [ ] Confirm `docs/interview/interview-questionnaire.md` (revised version with
      the introduction and glossary) is committed and pushed.

---

## 1. What changed about the plan

§9 of the spec, written 2026-08-29, planned a single `fact_chile_pepper` at
state × year sourced from the ERS Vegetables and Pulses Yearbook
(`vegetablespulsesyearbooktables.xlsx`). Profiling both sources says that is
wrong in one specific way:

**The ERS yearbook has no state dimension.** 108 sheets, all of them national ×
year, trade by country, or per-capita. Table 54 is the chile series; Table 32 is
bell. Neither carries a state breakdown, so the conformed `dim_state` join §9
was designed around cannot be satisfied from that workbook.

State-level chile comes from NASS Quick Stats instead, which does publish it.
That means **two chile facts, not one**:

| Fact | Grain | Source |
|---|---|---|
| `fact_chile_state` | state × year | NASS Quick Stats (five extracts below) |
| `fact_chile_national` | year | ERS Yearbook Table 54 |

They are not unionable. The national series is farm-weight million pounds with
imports, exports and per-capita availability; the state series is acres, cwt,
and dollars. They answer different questions and share only `dim_year`.

---

## 2. Sources as pulled

All five from NASS Quick Stats, Commodity = PEPPERS, Geographic Level = State,
2008–2025. One extract per data item, which is how Quick Stats exports them.

| Data item | File (GUID) | Rows (SURVEY/TOTAL) |
|---|---|---|
| `PEPPERS, CHILE - ACRES PLANTED` | `6B8B4B57-A8C4-30E9-BBF9-F8D1EDEEBF18` | 65 |
| `PEPPERS, CHILE - ACRES HARVESTED` | `CA70B843-82A4-3C22-BE2A-CF7C9BB6B0CC` | 66 |
| `PEPPERS, CHILE - YIELD, MEASURED IN CWT / ACRE` | `3CDFA62B-373A-3A19-95CB-881207B34387` | 65 |
| `PEPPERS, CHILE - PRICE RECEIVED, MEASURED IN $ / CWT` | `76EDE65A-E714-3F81-AF90-358FED3B79C2` | 65 |
| `PEPPERS, CHILE - PRODUCTION` (cwt, $, tons; plus bell and organic) | `0909B7A5-83F4-37C2-8555-C2F43EF0D893` | 1,880 raw |

A sixth download, `CA70B843-…-CF7C9BB6B0CC__1_.csv`, is a byte-identical
duplicate of the acres-harvested file. Discard it.

The production extract is the broad one — it also carries bell, organic,
under-protection and hydroponic items. Twenty distinct data items in 1,880 rows.
Only the six `PEPPERS, CHILE…` items are in scope; the bell items are a possible
comparison series and out of scope for v0.4.0.

ERS yearbook: sheet `Table 54-Chili Peppers, Pr`, 58 rows, 1980–2025, national.

---

## 3. Ingest filters — per extract, not global

Three filters get every extract to one clean row per state × year. **The third
one differs by extract and is the trap.**

1. `Program = SURVEY` — drops Census of Agriculture rows. Census years (2012,
   2017, 2022) return all 51 states with multiple domain breakdowns each. On
   acres harvested this inflates 66 rows to 289.
2. `Domain = TOTAL` — drops the `ORGANIC STATUS` breakdowns.
3. `Period` — **`MARKETING YEAR` for price; `YEAR` for everything else.**

Price publishes under both: 65 rows at `MARKETING YEAR` covering all years, plus
a 16-row `YEAR` series for 2008–2011 only, with different values (NM 2011 is
$33.90 marketing-year against $23.00 year). Taking both gives 16 duplicate
state-years and a silently wrong series.

**`Period` was a dead column in the cash rents extract** and is dropped at
ingest under §3.1. Here it is a filter key. The dead-column list is a property
of one extract, not of Quick Stats — re-profile it per source rather than
reusing §3.1.

---

## 4. Profile findings

**4.1 Coverage is five states plus a residual.** `SURVEY`/`TOTAL`, acres
harvested:

| State | Years published |
|---|---|
| New Mexico | 2008–2025, all 18 |
| California | 2008–2025, all 18 |
| Arizona | 2008–2018 only |
| Texas | 2008–2018, then 2024–2025 |
| Ohio | 2024–2025 only |
| `OTHER STATES` | 2016, 2020, 2024, 2025 |

Arizona and Texas going quiet after 2018 is publication intermittency, not
production ending — the same hazard as §3.12, and a line chart will render it
as a collapse to zero unless handled.

**4.2 Suppression is explicit, unlike the cash rents extract.** `(D)` and `(Z)`
appear as literal strings in `Value`. This is the reverse of §3.6, where all
suppression collapsed to blank and the distinction was unrecoverable. Here it
survives and the model should keep it: `(D)` is withheld for disclosure, `(Z)`
is a value below half the publication unit. Do not cast either to null without
recording which it was.

**4.3 `OTHER STATES` carries no State ANSI.** Same shape as the cash rents
residual rows. Follows D2 — route it to a separate table rather than joining it
to `dim_state`.

**4.4 Sources reconcile exactly.** Cross-checked against
`NM2025_Chile_Production03062026.pdf`:

- NM 2025 production 1,093,500 cwt ÷ 20 = **54,675 tons** — the PDF's figure.
- NM 2025 acres planted **8,200**, harvested **8,100** — the PDF's figures.
- NM 2024 planted **8,000**, harvested **8,000** — the PDF's figures.

Quick Stats and the NM state release agree to the pound. The PDF stays useful
for county-level splits and the fresh/processing breakdown, which Quick Stats
does not carry at state level.

**4.5 New Mexico, the series the article is about.**

| | 2008 | 2014 | 2019 | 2023 | 2025 |
|---|---|---|---|---|---|
| Acres planted | 12,300 | 8,100 | 9,100 | 8,800 | 8,200 |
| Acres harvested | 11,100 | 7,700 | 8,700 | 8,500 | 8,100 |
| Yield (cwt/acre) | 175 | 150 | 145 | 110 | 135 |
| Production (cwt) | 1,962,000 | 1,174,000 | 1,261,500 | 935,000 | 1,093,500 |
| Price ($/cwt) | 21.60 | 33.00 | 39.60 | 44.50 | 50.40 |

Harvested acreage −27% and yield −23% since 2008, compounding to **−44%
production**. Price has more than doubled over the same period. The decline is
not only less ground; it is less ground producing less per acre, against a
rising price that has not pulled acreage back in.

**4.6 Abandonment rate is computable and is not yet a finding.** Planted minus
harvested gives NM 9.8% abandoned in 2008 against 0.0% in 2024 and 1.2% in 2025.
Four low years against a noisy earlier series is thin, and 2024's exact zero is
more likely rounding to the nearest hundred acres than a true zero. Build the
measure, watch it, do not claim it.

**4.7 The project brief's headline figures do not survive checking.** The brief
says production fell from ~480M lbs in 2014 to ~175M lbs by 2022 while
per-capita consumption rose. ERS Table 54, million pounds farm weight:

| 2014 | 2017 | 2019 | 2021 | 2022 | 2023 | 2025 |
|---|---|---|---|---|---|---|
| 482.66 | 333.60 | 215.31 | 205.72 | 250.39 | 204.73 | 260.76 |

2014 is right. **2022 is 250.39, not ~175** — the series never reaches 175. And
per-capita availability is 7.18 lbs in 2014 against 7.45 in 2025, essentially
flat, having peaked at 8.02 in 2021. It has more than doubled since 1980 (3.05),
so "consumption rose" is a long-horizon claim and not a 2014-onward one.

The defensible framing: domestic production roughly halved since 2014 while
imports rose from 1,871M to 2,413M lbs and now supply about 90% of the total.
Imports absorbed the loss and held per-capita availability steady. Correct the
brief before any of this reaches the article.

**4.8 ERS Table 54 has three series breaks and a preliminary year**, all
footnoted on the sheet:

| Break | Year | What changes |
|---|---|---|
| Production source | 2018 | ERS estimates → NASS estimates |
| Price source | after 1999 | NM wet-basis average → NASS |
| Dry-basis conversion factor | 1988 | 5.0 → 8.0 |
| Preliminary | 2025 | most recent year flagged preliminary |

If the national fact is loaded, these need explicit flags in the same spirit as
`dim_year.survey_status` — not silent joins across a definition change.

**4.9 ERS deflates with a different index.** Table 54's constant-dollar column
uses the GDP implicit price deflator at 2017=100. The model uses CPI-U at
2025=100 (§7). Two real-dollar bases in one report is a footgun. Recommendation:
drop the ERS constant-dollar column and deflate the nominal price with the
existing `dim_year.deflator_to_base`, so every real figure in the report shares
one base and one index.

---

## 5. Design decisions to take (proposed D11–D14)

Not yet ratified. Each needs a decision-log entry and a version bump.

**D11 — `fact_chile_state` shape: wide, not tall.** One row per state × year,
with five typed measure columns: `acres_planted`, `acres_harvested`,
`yield_cwt_per_acre`, `price_usd_per_cwt`, `production_cwt`,
`production_value_usd`. Rationale: the metrics have five different units, and
§9's existing rule forbids mixing units in one measure column. A metric
dimension in the style of `dim_land_category` would require exactly that. ~65
rows either way.

**D12 — `OTHER STATES` to a separate residual table**, per D2 precedent. Four
rows. The alternative — a residual flag on the state fact — reads leaner but
breaks the rule that residuals never sit in the same table as real entities.

**D13 — drop `is_chile_producing` from `dim_state`** (§6.4 deferred it to this
ingest). The producing set moves: Arizona left after 2018, Ohio arrived in 2024.
A boolean freezes something that changes, and the fact table's own contents
answer the question. Recommendation is to delete the column rather than build
it.

**D14 — `dim_year.survey_status` needs renaming.** `SUSPENDED` in 2015 and 2018
describes the Cash Rents survey only. Chile published normally in both years. As
the attribute name stands, any chile visual that inherits it — or that applies
the **Show items with no data** build requirement from §6.5 — will break a line
that has no break in it. Rename to `cash_rents_survey_status`, or move the
attribute onto the cash rents fact. **This is the one item that can silently
corrupt a published chart, so take it before building the chile visuals.**

---

## 6. Next action

Build the Power Query staging for `fact_chile_state`, one extract at a time,
acres planted first. Same pattern as `src_cash_rents`: parameterized folder and
file, no type detection on load, explicit filters, cast last.

Open question to settle at the first step: whether the five extracts are five
staging queries merged on state × year, or one appended query pivoted on data
item. Appending is closer to the cash rents pipeline and handles a sixth data
item arriving without a new query; merging is flatter to read. Decide before
writing, not after.
