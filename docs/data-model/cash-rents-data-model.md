# USDA NASS County Cash Rents — Data Model Specification

**Version:** 0.3.4
**Status:** Model built and validated. Thirteen measures written and validated.
Two report pages built for the 2026-09-19 producer interview; report layer
proper not started. One published figure withdrawn — see §10.6.
**Last updated:** 2026-09-16
**Owner:** Aaron / Heat & Harvest Data Desk
**Repo:** `github.com/chaferoc/american-chile-economy`, at
`docs/data-model/cash-rents-data-model.md`

---

## 1. Purpose and scope

This document specifies the dimensional model for the USDA NASS county-level
cash rents series as loaded into the "American Chile Economy" Power BI report.
It is the authoritative record of grain, keys, naming, and modeling decisions,
and is written to be quotable in the article's methodology section.

Scope of this version: the county cash rents fact and its dimensions. The ERS
chile pepper series is designed for but not ingested; see §9.

---

## 2. Source

| Field | Value |
|---|---|
| Agency | USDA National Agricultural Statistics Service (NASS) |
| Series | Cash rent paid, by county, $/acre |
| Extract | Quick Stats CSV, `9A9F55D7-E267-38C6-ACB9-DF106291B5A7.csv` |
| Extract size | 46,942 rows × 24 columns, 7.6 MB |
| Coverage | 2008–2026, 49 states (Alaska excluded by survey design) |
| Land categories | Irrigated cropland, non-irrigated cropland, pastureland |
| Methodology reference | *Cash Rents Methodology and Quality Measures*, NASS, released 2025-08-29 (`crntqm25.pdf`) |
| Instrument reference | *Cash Rents and Leases 2024* questionnaire, OMB 0535-0002 (`2024CashRentsQuestionnaire.pdf`) |

Survey design context relevant to the model: the survey is voluntary, sample
size is approximately 242,000 operations, and the target population is farms
and ranches with $1,000 or more in agricultural sales that rent land on a cash
basis. Rates are ratio estimates — total rent paid divided by total acres
rented — not averages of per-operation rates.

### 2.1 Why a rate exists for a year that has not finished

Carry this into every version of the article, in the reader's language and near
the first chart. The reader has never opened a NASS release and will assume a
2026 figure is a forecast.

Cash rent is a contracted price, not a harvest outcome. The operator and the
landowner agree the rate before the crop year starts, so it is knowable in
advance. NASS collects it mid-February through June, asking what the operation
*will* pay — the questionnaire is written in the future tense
(`2024CashRentsQuestionnaire.pdf`, item 4: "What will be the cash rent/lease per
acre") — and publishes county estimates in August. A 2026 rate published in
August 2026 is therefore a recorded agreement, not a projection, and nothing
about it waits on the growing season.

Two consequences the article should state plainly: a rent figure and a
production figure for the same year are measured at opposite ends of it, and a
rent series responds to expectations about a year while a production series
responds to what the weather actually did.

---

## 3. Profile findings that constrain the model

These are the source characteristics the model is built around. Established by
profiling the extract on 2026-08-29.

**3.1 Dead columns.** Eleven of 24 columns carry no information and are dropped
at ingest: `Program`, `Period`, `Geo Level`, `Commodity`, `Domain`,
`Domain Category`, `watershed_code` (all single-valued), and `Week Ending`,
`Zip Code`, `Region`, `Watershed` (all empty).

**3.2 Two survey suspensions.** 2015 and 2018 are absent. These are NASS
program-wide suspensions, not extract gaps — confirmed absent for all 49 states,
not merely for some regions. 17 of 19 years in range are present.

The cause is statutory and belongs in the methodology section. Section 2110 of
the amended 2008 Farm Bill set a floor of "not less frequently than once every
other year," which the 2018 Farm Bill raised to annual (§2, and the same
sentence quoted in `crntqm25.pdf`). Under the biennial floor NASS could skip a
county-level year and did so twice. In both 2015 and 2018 NASS published
national, regional and state cash rents and did not publish county estimates;
for 2015 it announced this in advance and resumed in 2016. Because this extract
is county-level, the skips appear as missing years. **A producer who filled out
a form in 2015 or 2018 was not imagining it** — a state-level estimate was
published from that collection. No year after 2018 is missing, which is the
amendment taking effect.

**3.3 2008 is a partial year.** 1,592 rows against ~2,890 from 2009 onward, and
135 irrigated values against ~800, across 45 states. Not a comparable baseline;
exclude from trend claims or annotate.

The shortfall is county coverage in the program's first year, not suppression
and not the 20,000-acre eligibility threshold. Missouri published 95 counties in
2008 and 114 in 2009; Iron County has no 2008 row for any land category and then
publishes in every subsequent year. This matters for any per-county visual: a
2008 gap and a 2015 gap render identically and mean opposite things — *the
county was not in the survey yet* versus *the survey did not run*. Annotate
both, separately.

**3.4 The residual grain changes in 2021.** Two mutually exclusive rollup
labels exist:

| Label | Years | Rows | Grain | `County ANSI` | `Ag District Code` |
|---|---|---|---|---|---|
| `OTHER (COMBINED) COUNTIES` | 2009–2020 | 2,771 | state × ag district | blank | real district code |
| `OTHER COUNTIES` | 2021–2026 | 289 | state | blank | `99` |

2008 has neither. This break coincides with the introduction of CV publication
and with the switch to model-based county estimation. One methodological event,
three visible symptoms.

**3.5 CVs begin in 2021.** Zero coefficients of variation 2008–2020; populated
from 2021 forward. A CV never appears without a value. A small number of
post-2021 values lack a CV (7 of 700 irrigated values in 2026), so the pairing
is not guaranteed.

**3.6 Suppression is an empty string.** No `(D)`, `(NA)`, or other marker
appears anywhere in the six measure columns. The extract does not distinguish
"withheld for disclosure" from "not estimated" from "not applicable." All three
collapse to blank and the model cannot recover the distinction.

**3.7 No row is fully empty.** Every row publishes at least one land category:
14,981 rows carry one, 25,451 carry two, 6,510 carry all three. The source is
already sparse by construction.

**3.8 Thousands separators in irrigated values.** 130 irrigated values are
formatted `"1,050"`. This is the only measure column that parses as text.

The hazard is locale, not failure. A type conversion that relies on the ambient
locale parses `"4,000"` as 4000 where comma is a thousands separator and as 4.0
where comma is the decimal separator. Neither raises an error. The same PBIX
refreshed on two machines therefore yields different values, and only in the
130 highest-rent irrigated counties, so the distortion is systematic rather
than random. Commas are stripped with `Text.Replace` before any conversion, and
`Number.FromText` is used bare so an unparseable value surfaces as an Error
rather than a null (§5, step 4).

**3.9 County ANSI is not a key.** It holds only 265 distinct values — the
3-digit within-state FIPS. County *name* is not a key either: 408 of 1,719
real county names appear in more than one state. (Counting the two rollup
labels as names gives 410 of 1,721; the smaller figures are the ones to quote.)
`State ANSI` + `County ANSI` yields 2,938
distinct entities and is unique within year (zero duplicates).

**3.10 Ag district code is only unique within state.** Across real counties, 23
codes cover 83 district names in 306 state × district pairs. A 24th code, `99`,
appears only on the 2021–2026 state-residual rows and is a sentinel, not a
district; counting it gives the whole-extract figures of 24 codes / 84 names /
355 pairs. `dim_geography` carries the 23-code set.

**3.11 County-to-district assignment is stable.** No county changes ag district
across the 17 years. No slowly-changing-dimension logic required.

**3.12 Publication is intermittent.** 1,313 counties publish in all 17 years,
785 in 16, but 44 publish in exactly one year with a long tail between. The
geography dimension must be built from the union of all years.

**3.13 Value ranges and precision.** All values are published to half-dollar
precision (one decimal place maximum).

| Category | Min | Median | Max | Populated |
|---|---|---|---|---|
| Irrigated cropland | $16.00 | $147.00 | $4,030.00 | 12,583 |
| Non-irrigated cropland | $4.50 | $56.50 | $385.00 | 39,362 |
| Pastureland | $0.30 | $19.50 | $153.00 | 33,468 |

**3.14 CV distribution.** Medians 5.5–6.9%. Pastureland is noisiest: 406
county-years above 20% CV, 84 above 30%. Extreme is 118.7% (New Mexico state
residual, non-irrigated, 2025).

All three figures are whole-extract and include residual rows. County-scoped,
pastureland is 383 above 20% and 75 above 30%, and the median range is
5.4–6.9%. Quote the county-scoped figures in any claim about counties (§10.5).

**3.15 Non-standard entities.** Hawaii publishes merged entities such as
`MAUI & KALAWAO` that carry a County ANSI and behave as counties. Retained
as-is per decision D8. Rhode Island appears only in 2026, and only as a state
residual row.

---

## 4. Decision log

Decisions taken 2026-08-29. Change requires a version bump and an entry in §11.

| ID | Decision | Resolution |
|---|---|---|
| D1 | Fact density | **Sparse.** Drop null-value rows. Dense coverage grids derived in DAX via `CROSSJOIN`, not materialized. |
| D2 | Residual rows | **Separate table.** `fact_cash_rent_residual`, never mixed into the county fact. |
| D3 | `dim_year` rows | **19 rows**, 2008–2026 inclusive, with explicit `SUSPENDED` status for 2015 and 2018. Visuals break the line at suspension years. Status column named `cash_rents_survey_status` per D14. |
| D4 | CV placement | **Column on the fact.** Plus derived `confidence_band`: `HIGH` <10%, `MODERATE` 10–20%, `LOW` >20%. |
| D5 | `geo_key` format | **5-character text FIPS** (`state_ansi` + `county_ansi`, zero-padded). Joins natively to Power BI maps, TIGER shapefiles, and other USDA county datasets. |
| D6 | `dim_state` | **Build now**, before the ERS ingest, as a conformed dimension. |
| D7 | Currency | **Both.** Nominal stored on the fact; real dollars derived in DAX from a CPI-U deflator on `dim_year`. See §7. |
| D8 | Non-standard entities | **Keep as-is.** No normalization of Hawaii merged entities. |
| D9 | Load scope | **All 49 states**, full 2008–2026 range. |
| D10 | Artifact location | **Versioned markdown in a git repo**, current copy kept in the H&H Data Desk project. |
| D14 | `dim_year` survey status naming | **Rename to `cash_rents_survey_status`.** Taken 2026-09-16. Not moved onto the fact: suspension is a property of a year, not of a rent observation, and moving it would repeat the value 79,064 times. See §6.5. |

---

## 5. Reshape specification

As built. This ordering differs from the v0.1.x plan in two places, both noted
below; the end state is identical.

Target grain: one row per geography × year × land category, where a value was
published.

**Staging query `src_cash_rents`** (not loaded to the model):

| # | Step | Rows out |
|---|---|---|
| 1 | `Csv.Document` with `QuoteStyle.Csv`, `Encoding=65001`, no type detection | 46,942 |
| 2 | Promote headers | 46,942 |
| 3 | Remove the eleven dead columns (§3.1) via `Table.RemoveColumns` | 46,942 |
| 4 | `Table.UnpivotOtherColumns`, pinning the seven identity columns | 281,652 |
| 5 | Strip commas, empty string → null, cast to number (§3.8) | 281,652 |
| 6 | Join the measure-name map → `land_category_code` + `metric` | 281,652 |
| 7 | `Table.Pivot` on `metric`, no aggregation → `rent_usd_per_acre`, `cv_pct` | 140,826 |
| 8 | Drop rows where `rent_usd_per_acre` is null (D1) | **85,413** |

**Branch queries**, each a `Reference` to `src_cash_rents`:

| Query | Filter | Rows |
|---|---|---|
| `fact_cash_rent` | `County ANSI` is present | 79,064 |
| `fact_cash_rent_residual` | `County` is one of the two rollup labels | 6,349 |
| `dim_geography` | `County ANSI` present, then `Table.Distinct` | 2,938 |
| `dim_state` | no filter — Rhode Island exists only as a residual row (§3.15) | 49 |

`dim_year` and `dim_land_category` are authored, not derived (§6.5, §6.6).

**Two deviations from the v0.1.x order, and why.**

*Unpivot before cleaning and casting.* The plan cleaned six measure columns and
then unpivoted. Unpivoting first collapses them into one column, so the comma
strip and the type cast are single transformations rather than six, and they
stop being irrigated-specific — a comma appearing in any future measure column
is handled by the same code.

*Drop nulls before routing.* The plan routed into three streams and then
dropped nulls in each. Filtering once upstream applies the rule in one place and
puts a validation checkpoint at 85,413 while all three streams still share a
lineage, so a bad count identifies the filter rather than the routing. This is
safe only because no row carries a CV without a value (§3.5); that was verified
before reordering.

**Three design rules the transformations follow.** Each appears more than once
above and each was chosen so a source change fails loudly:

1. *Name the stable set, let the volatile set flow.* Remove Columns names the
   dead columns so a new NASS column arrives visible; Unpivot Other Columns
   pins the fixed geography block so a new measure column is unpivoted
   automatically.
2. *Enumerate rather than parse.* The six measure column names map to
   `land_category_code` through an explicit lookup table, not a delimiter split
   or a `Text.Contains` chain. `"NON-IRRIGATED"` contains `"IRRIGATED"`, and a
   test order dependency is not a correctness argument.
3. *Prefer the loud failure.* Pivot uses no aggregation, so a duplicate key
   becomes an Error rather than a silent sum. The two branch filters use
   different tests — structural for counties, by-label for residuals — so an
   unrecognized rollup label lands in neither stream and the reconciliation
   below stops balancing.

**Load-validation targets.** Implemented as the `chk_row_counts` query, 19
assertions covering row counts, the fact/residual reconciliation, CV
population, referential integrity on all six foreign keys, `dim_geography`
key uniqueness, and the two `dim_year` suspension-year invariants (D14). All 19
pass as of 2026-09-16.

| Stream | Rows |
|---|---|
| `fact_cash_rent` (counties) | 79,064 |
| `fact_cash_rent_residual` — district level | 5,562 |
| `fact_cash_rent_residual` — state level | 787 |
| **Total populated combinations** | **85,413** |
| (Dense grid, for reference — not loaded) | 140,826 |

CV population splits 29,346 county / 749 residual, totalling 30,095.

---

## 6. Star schema

### 6.1 `fact_cash_rent`

Grain: one county × year × land category with a published rate. ~79,064 rows.

| Column | Type | Null | Note |
|---|---|---|---|
| `geo_key` | text(5) | no | FK → `dim_geography` |
| `year_key` | int | no | FK → `dim_year` |
| `land_category_key` | int | no | FK → `dim_land_category` |
| `rent_usd_per_acre` | decimal(6,1) | no | nominal dollars, as published |
| `cv_pct` | decimal(5,1) | yes | null for all years before 2021 by design |

### 6.2 `fact_cash_rent_residual`

Grain: one residual entity × year × land category. ~6,349 rows. Holds values
NASS published only in aggregate because the component counties did not meet
disclosure or publication standards.

| Column | Type | Null | Note |
|---|---|---|---|
| `state_key` | text(2) | no | FK → `dim_state` |
| `ag_district_code` | text(2) | yes | null when `residual_level` = `STATE` |
| `year_key` | int | no | FK → `dim_year` |
| `land_category_key` | int | no | FK → `dim_land_category` |
| `residual_level` | text | no | `DISTRICT` (2009–2020) or `STATE` (2021–2026) |
| `rent_usd_per_acre` | decimal(6,1) | no | |
| `cv_pct` | decimal(5,1) | yes | |

This table is never unioned with `fact_cash_rent`. Its two residual levels are
also not comparable to each other across the 2021 boundary.

### 6.3 `dim_geography`

Grain: one published county entity. 2,938 rows, built from the union of all
years.

| Column | Type | Note |
|---|---|---|
| `geo_key` | text(5) | PK. `state_ansi` + `county_ansi`, zero-padded. Example: `29093` = Iron County, MO |
| `state_ansi` | text(2) | FK → `dim_state` |
| `county_ansi` | text(3) | |
| `county_name` | text | not unique across states |
| `ag_district_code` | text(2) | unique only within state |
| `ag_district_name` | text | |

No SCD handling: district assignment is stable across the full period (§3.11).

### 6.4 `dim_state`

Grain: one state. 49 rows. Conformed dimension shared with the future chile
fact.

| Column | Type | Note |
|---|---|---|
| `state_key` | text(2) | PK, state ANSI/FIPS |
| `state_name` | text | |
| `state_abbr` | text(2) | |
| `nass_region` | text | Northeast, Lake, Corn Belt, Northern Plains, Appalachian, Southeast, Delta, Southern Plains, Mountain, Pacific — per `crntqm25.pdf` |
| `is_chile_producing` | bool | **not yet built.** Deliberately absent rather than defaulted to false; set at ERS ingest, when it will also be clear whether a boolean or a first-production-year is the right shape. |

### 6.5 `dim_year`

Grain: one calendar year. 19 rows, 2008–2026 inclusive.

| Column | Type | Note |
|---|---|---|
| `year_key` | int | PK |
| `year` | int | |
| `cash_rents_survey_status` | text | `PUBLISHED`; `SUSPENDED` for 2015 and 2018. Survey-specific by name, by design — see below (D14). |
| `is_partial_coverage` | bool | true for 2008 only (§3.3) |
| `cv_published` | bool | false ≤2020, true ≥2021 |
| `estimation_method` | text | `SURVEY_DIRECT_EXPANSION` ≤2020, `BAYESIAN_SMALL_AREA` ≥2021 — see §10.1 |
| `residual_grain` | nullable text | `NONE` (2008), `DISTRICT` (2009–2020), `STATE` (2021–2026), **null for 2015 and 2018**. Ascribed `type nullable text`, not `type text` — see below. |
| `cpi_u_annual` | decimal | BLS CPI-U annual average, US city average, all items |
| `deflator_to_base` | decimal | see §7 |

Carrying 2015 and 2018 as explicit suspension rows is what makes the break
visible in a line chart. Without them, a visual connects 2014 straight to 2016
and implies continuity where the survey did not run. This is the one table in
the model that asserts rows the source does not contain, and that is its
purpose: harvest a calendar from the facts and missing periods become invisible
by construction.

`residual_grain` is null in the two suspension years, revised from the v0.1.x
rule that assigned them `DISTRICT`. The column describes what the extract
contains for a year, and a suspended year contains nothing — labelling it
`DISTRICT` would claim a rollup structure that was never published. This is
§3.6 in reverse, and the fix is the same: do not let "not applicable" share a
token with a real value. `cv_published` and `estimation_method` stay populated
for those years, because they describe the methodology regime in force, which
existed whether or not the survey ran. The same reasoning nulls
`ag_district_code` on `STATE` residual rows, where the source carries sentinel
code `99` (§3.10).

That null is also why `residual_grain` is ascribed `type nullable text` rather
than `type text`. The ascription was wrong from the first build and sat
harmless behind a cached step result; editing the step forced a real
evaluation and the column returned errors on all 19 rows. A type ascription is
a claim about contents, and M does not check it until something makes it.

**The status column is named for its survey, not for the calendar.** `dim_year`
is a conformed dimension and the chile fact attaches to it. Cash Rents was
suspended in 2015 and 2018; chile published normally in both years. A column
called `survey_status` reading `SUSPENDED` would be inherited by every chile
visual, and combined with the **Show items with no data** requirement below it
would open a gap in a series that has no gap. The general rule: a conformed
dimension carries attributes true of the calendar, and where an attribute
belongs to one source, its name must say so. Renamed under D14; the rename
broke three dependents, none of which the model reported at open — see the §11
entry for 0.3.4.

**The 19-row design is necessary but not sufficient.** It makes the suspension
break *representable*; it does not make it *appear*. Power BI drops categories
with no data from an axis, so a line or bar chart of rent by year will still
connect 2014 straight to 2016 unless the visual has **Show items with no data**
enabled on the year field. Model design and report design each do half the job
here, and the model half fails silently on its own. Every time-series visual in
this report must have that setting on; it is a build requirement, not a
preference.

`dim_year` has no date column, so it cannot be marked as a date table and DAX
time intelligence does not apply. This is correct for an annual series — a date
table would need 365 rows per year to represent a once-yearly measurement — but
it means `Rent YoY Pct` is written with `year_key - 1` arithmetic rather than
`SAMEPERIODLASTYEAR`.

### 6.6 `dim_land_category`

Grain: one land category. 3 rows.

| `land_category_key` | `land_category_code` | `land_category_label` | `is_cropland` |
|---|---|---|---|
| 1 | `CROPLAND_IRRIGATED` | Irrigated cropland | true |
| 2 | `CROPLAND_NON_IRRIGATED` | Non-irrigated cropland | true |
| 3 | `PASTURELAND` | Pastureland | false |

### 6.7 Relationships

```
dim_state (1) ──< (*) dim_geography (1) ──< (*) fact_cash_rent
dim_state (1) ──────────────────────────────< (*) fact_cash_rent_residual
dim_year  (1) ──────────────────────────────< (*) fact_cash_rent
dim_year  (1) ──────────────────────────────< (*) fact_cash_rent_residual
dim_land_category (1) ──────────────────────< (*) fact_cash_rent
dim_land_category (1) ──────────────────────< (*) fact_cash_rent_residual
```

Seven relationships, all one-to-many, all single cross-filter direction,
all active. Relationship autodetect is disabled on the file so none of them was
inferred.

Single direction is load-bearing rather than stylistic. `dim_state`,
`dim_year`, and `dim_land_category` each reach both facts, so the undirected
graph contains loops. Single direction makes them harmless: filters travel only
dimension to fact and no return path exists. One bidirectional relationship
opens a return path — filtering to pastureland would filter `fact_cash_rent`,
which would filter `dim_year` to the years pastureland was published, which
would filter `fact_cash_rent_residual`. A slicer on one fact would silently
reshape the other. `dim_state → dim_geography` is a deliberate snowflake: it is what lets
state-level filters reach the county fact and lets the future chile fact share
a state dimension with it. The denormalized alternative — folding state
attributes into `dim_geography` — would leave the chile fact with a private
state table and no cross-filtering.

---

## 7. Real-dollar measures (D7)

Nominal dollars are stored on the fact and never modified. Real dollars are
computed in DAX so the deflator, base year, and index series can change without
reloading data.

- **Index:** BLS Consumer Price Index for All Urban Consumers (CPI-U), US city
  average, all items, 1982-84=100, annual average. Series `CUUR0000SA0`.
- **Base year:** 2025.
- **Deflator:** `deflator_to_base = cpi_u_annual[2025] / cpi_u_annual[year]`,
  stored as a column on `dim_year`.
- **Measure:** `Avg Real Rent per Acre` = nominal rent x `deflator_to_base`.

### 7.1 Series as loaded

| Year | CPI-U | Deflator | Year | CPI-U | Deflator |
|---|---|---|---|---|---|
| 2008 | 215.303 | 1.4953 | 2018 | 251.107 | 1.2821 |
| 2009 | 214.537 | 1.5006 | 2019 | 255.657 | 1.2593 |
| 2010 | 218.056 | 1.4764 | 2020 | 258.811 | 1.2439 |
| 2011 | 224.939 | 1.4312 | 2021 | 270.970 | 1.1881 |
| 2012 | 229.594 | 1.4022 | 2022 | 292.655 | 1.1001 |
| 2013 | 232.957 | 1.3820 | 2023 | 304.702 | 1.0566 |
| 2014 | 236.736 | 1.3599 | 2024 | 313.689 | 1.0263 |
| 2015 | 237.017 | 1.3583 | 2025 | 321.943 | 1.0000 |
| 2016 | 240.007 | 1.3414 | 2026 | 331.180 | 0.9721 |
| 2017 | 245.120 | 1.3134 | | | |

### 7.2 Two years are not twelve-month averages

`cpi_u_annual` holds three different averaging windows under a column name that
says "annual." Both exceptions are disclosed in the article's methodology
section.

**2025 is an eleven-month average.** BLS did not collect October 2025 owing to
the lapse in appropriations; the published annual average of 321.943 is the
mean of the eleven months that exist, which reproduces exactly. Because 2025 is
the *base*, the effect is a constant multiplier of roughly 0.07% on every real
figure. Trends are unaffected; the "in 2025 dollars" label is marginally off.

**2026 is a seven-month average.** No annual average exists. The loaded value
331.180 is the mean of January through July 2026. The alternative was null,
which would drop all 2,664 of the 2026 fact rows out of every real-dollar
visual. `crntqm25.pdf` puts collection between mid-February and June, so a 2026
cash rent is already priced in roughly this window — the partial average is
arguably better aligned to the survey than a full-year figure would be. Revisit
when BLS publishes the 2026 annual average (§10.2).

Every chart and article figure states whether it is nominal or real. Trend
claims spanning 2008-2026 use real dollars; single-year comparisons use
nominal.

### 7.3 The rule exists because the gap is large, and the endpoint is worse

A nominal line chart of Iron County pastureland was published to social media on
2026-09-14 spanning 2008-2026, which is the exact case this rule forbids. Two
distortions compound, and the second is bigger than the first.

*Deflation.* 2009 to 2025, nominal $13.00 to $43.50, is +235%. In 2025 dollars
it is $19.51 to $43.50, or +123%. Just over half the nominal rise is the
dollar.

*Endpoint choice.* Ending the same series at 2026 instead of 2025 gives +69%
nominal and **+10% real** ($19.51 to $21.39) — against +235% from the same
starting point. One endpoint is a near-quadrupling, the other is essentially
flat in real terms, and the difference is a single year chosen from a series
whose last six values are 18.5, 16.0, 13.5, 16.5, 43.5, 22.0.

The stated rule — real dollars for full-period claims — handles the first. It
does not handle the second. A trend claim on a single county must also state
its endpoints and survive moving them by a year; where it does not, the claim
belongs to the endpoint rather than to the trend. See §10.6.

---

## 8. Naming conventions and measures

### 8.1 Conventions

| Element | Convention | Example |
|---|---|---|
| Tables | `fact_` / `dim_` prefix, snake_case | `fact_cash_rent` |
| Surrogate and foreign keys | `_key` suffix | `geo_key`, `year_key` |
| Source natural keys | `_ansi` or `_code` suffix | `county_ansi`, `ag_district_code` |
| Measure columns | unit in the name | `rent_usd_per_acre`, `cv_pct` |
| Coded values | UPPER_SNAKE | `CROPLAND_IRRIGATED`, `SUSPENDED` |
| DAX measures | PascalCase with spaces | `Avg County Rent per Acre` |

DAX measures use spaces so a measure is never mistakable for a column inside a
formula: `[Land Category In Context]` is a measure, `fact_cash_rent[cv_pct]` is
a column.

All measures live in `_Measures`, an empty table created via Enter Data with
its single column hidden. A measure computes identically wherever it is filed,
so the home table is a filing decision — but scattering measures across two
fact tables makes them hard to find, and `Rent vs State Median` will reference
more than one table anyway.

### 8.2 Measures as built

| Measure | Notes |
|---|---|
| `Land Category In Context` | Hidden. Returns whether a single land category is in filter context. Extracted so the guard is defined once rather than copied into every measure. |
| `Avg County Rent per Acre` | Unweighted mean of county estimates. Blanks unless a land category is in context. |
| `Avg Real Rent per Acre` | `AVERAGEX` deflating each row by its own year, not `AVERAGE x` a single deflator. |
| `Rent YoY Pct (Unmatched)` | Naive year-over-year. Retained for comparison; **not for publication.** |
| `Rent YoY Pct (Matched Counties)` | Year-over-year on the constant panel of counties publishing in both years. **The publishable one.** |
| `Counties Reporting` | `DISTINCTCOUNT` of `geo_key`. Unguarded — see §8.6. Non-additive across land categories by design. |
| `Low Confidence Share` | Share of estimates with `cv_pct` > 20, denominated on estimates that carry a CV, not on all reporting counties. Blanks before 2021. |
| `Median CV` | `MEDIANX` over rows with a CV. Median rather than mean because of the right tail (§3.14). Blanks before 2021. |
| `Rent vs State Median` | County rate against the median county rate in its state, same year and category. Guarded on land category and on a blank county rate. See §8.6. |
| `State Median CV` | Median CV across the counties publishing in the same state, year and category. Peer context for a single county's CV, which on its own is an uninterpretable percentage. Guarded on land category; blanks before 2021. |
| `State Median Rent per Acre` | The peer median itself, in dollars — the denominator inside `Rent vs State Median`, surfaced so a table can show it. Iterates the fact, not the dimension (§8.6). |
| `County Rank in State` | County's rank among counties publishing in its state, year and category. `RANKX`, `DESC`, ties `Skip`. Guarded on land category and on a blank county rate. See §8.6. |
| `Counties Reporting in State` | Denominator for the rank. Guarded on land category, unlike `Counties Reporting`, because a rank exists only within a category. Deliberately *not* guarded on a blank county rate: in 2008 Iron County has no estimate and 76 Missouri counties do, and 76 is the honest answer. |

All thirteen measures validated against values computed independently from the
extract — the first nine on 2026-09-12, the four peer-context measures on
2026-09-13. Regression baseline, county fact only:

| Measure | Scope | Value |
|---|---|---|
| `Counties Reporting` | unfiltered | 2,938 |
| `Counties Reporting` | irr / non-irr / pasture, all years | 1,223 / 2,795 / 2,665 |
| `Counties Reporting` | 2025, all categories | 2,690 |
| `Low Confidence Share` | all years, all categories | 2.6% (766 of 29,346) |
| `Low Confidence Share` | 2025, pastureland | 4.9% |
| `Median CV` | all years, irr / non-irr / pasture | 6.2 / 5.4 / 6.9 |
| `Rent vs State Median` | Iron MO, 2025, pastureland | +9.4% (43.5 vs 39.75, n=106) |
| `Rent vs State Median` | Doña Ana NM, 2026, irrigated | +78.9% (296.0 vs 165.5, n=10) |
| `State Median CV` | MO pastureland, 2021 / 2023 / 2025 | 5.1 / 6.1 / 5.85 |
| `State Median Rent per Acre` | MO pastureland, 2020 / 2025 | $35.25 / $39.75 |
| `County Rank in State` | Iron MO pastureland, 2025 / 2023 | 40 / 106 |
| `Counties Reporting in State` | MO pastureland, 2008 / 2025 / 2026 | 76 / 106 / 104 |
| `Avg County Rent per Acre` | Iron MO pastureland, 2021-2026 | 18.5 / 16.0 / 13.5 / 16.5 / 43.5 / 22.0 |
| `Median CV` | Iron MO pastureland, 2021-2026 | 6.0 / 12.0 / 16.1 / 7.0 / 28.7 / 8.3 |

Both CV measures return blank for every year 2008–2020.

A finding that came out of the two CV measures and belongs in the article:
median CV rises since 2021 across every category, and it rises on a constant
panel too — the 1,197 counties publishing pastureland in all six years. But it
is a drift, not a monotone climb, and v0.3.1 stated it too strongly. The
pastureland trajectory:

| | 2021 | 2022 | 2023 | 2024 | 2025 | 2026 |
|---|---|---|---|---|---|---|
| All publishing counties | 6.0 | 6.5 | 6.9 | 7.1 | **7.4** | 7.3 |
| Constant panel (n=1,197) | 5.4 | 5.9 | 6.1 | 6.6 | **7.0** | 6.8 |
| Panel low-confidence share | 0.9% | 1.2% | 1.8% | 2.1% | **3.8%** | 3.1% |
| Missouri | 5.1 | 5.9 | 6.1 | 5.6 | 5.85 | 5.95 |

**2026 turns down on all three national measures**, and Missouri is not
monotonic at all — it dips in 2024 and gains under a point across the whole
period. The v0.3.1 text read "from 2021 to 2026" against the values 5.4 → 7.0
and 0.9% → 3.8%, which are the 2021 and **2025** figures; 2026 is 6.8 and 3.1%.
The publishable claim is *up about a point and a half nationally since 2021,
less in Missouri, easing in 2026* — never "every year."

Composition explains the level gap between the panel and the full set but not
the trend. The causal half is unverified; `crntqm25.pdf` gives a 47.2% national
response rate for 2025 but the response-rate trend has not been checked.

### 8.3 Why the first measure is not called `Avg Rent per Acre`

The v0.1.x plan named it `Avg Rent per Acre`. That name asserts "the rent per
acre." The measure is an unweighted mean of county estimates, in which a county
renting 400 acres counts the same as one renting 400,000.

The constraint is not fixable with this extract. Weighting requires acres
rented, and the Quick Stats pull contains rates and CVs only. A properly
weighted average is not a harder measure to write; it is an impossible one
without a second data source. So the name carries the caveat: a reader who
drags `Avg County Rent per Acre` onto a canvas next to a state slicer can see
that it is a mean across counties, not a state estimate.

### 8.4 Why measures blank instead of answering

`Avg County Rent per Acre` returns `BLANK()` when no land category is in filter
context. Unfiltered, the arithmetic mean across all 79,064 rows is $73.57 — a
number that describes nothing, since the three category means are $186.11,
$103.97 and $23.29 nominal and the grand mean is an artifact of how many rows
each category happens to contribute.

This is consistent with the rest of the build: make the wrong answer
unavailable rather than merely discouraged. The same instinct sets Summarize by
to None on every rate column, so dragging `rent_usd_per_acre` onto a visual
cannot silently produce a sum of per-acre rates.

### 8.5 Why there are two year-over-year measures

The county panel changes every year (§3.12), so a year-over-year change in the
unweighted mean mixes rate change with composition change. Non-irrigated
cropland, all counties versus the constant panel:

| Year | Unmatched | Matched | Gap |
|---|---|---|---|
| 2009 | −20.7% | +2.3% | 23.0 pp |
| 2021 | −0.6% | +2.0% | 2.6 pp |
| 2025 | +4.0% | +1.3% | 2.7 pp |
| typical year | | | 0.4–1.5 pp |

2009 is the extreme case: 2008 covered 1,236 non-irrigated counties against
2,179 in 2009, and the counties it missed were cheaper, so the naive
calculation reports a 20% collapse in rents that did not occur (§3.3). Ordinary
years differ by one to three points — small enough to survive review, large
enough to change a claim. 2025 reads triple its matched value.

`Rent YoY Pct (Matched Counties)` restricts both sides of the comparison to
counties publishing in both years, via `INTERSECT` and `KEEPFILTERS` so that
state and category slicers still apply. **Article figures use the matched
measure.** The unmatched one stays in the model as the comparison that
justifies the choice.

Both measures test `cash_rents_survey_status` on the current *and* prior
year. Testing only the prior year is a real bug that was caught during the
build: in 2015 the prior year (2014) was published and the current year was
blank, so
`DIVIDE ( BLANK() − 84.77, 84.77 )` returned exactly −100% — a total collapse in
cash rents, rendered without complaint.

### 8.6 Peer groups iterate the fact, not the dimension

`Rent vs State Median` builds a peer group: every county in the same state,
same year, same land category. Three versions were wrong before one was right,
and each failure mode is worth keeping.

*Iterate the fact.* The working version is
`MEDIANX ( VALUES ( fact_cash_rent[geo_key] ), ... )`. The first version
iterated `VALUES ( dim_geography[geo_key] )` instead. The dimension holds every
county that ever published anything; the fact, under filter context, holds only
the counties that published *this* year in *this* category. Missouri has 114
county entities but 106 pastureland estimates in 2025, and the 8 non-publishers
entering the iteration moved the median from 39.75 to 39.0 — reporting Iron
County as 11.5% above its peers instead of 9.4%. This is §3.12 resurfacing in
the measure layer: any measure that iterates a dimension to build a peer group
inherits counties that published nothing. The error is small, plausible, and
invisible without an external check.

*`REMOVEFILTERS` on named columns, not `ALLEXCEPT` on the table.* `ALLEXCEPT`
operates on the expanded table, so `ALLEXCEPT ( dim_geography, ... )` also
cleared the `dim_state` filter and silently widened the peer group from one
state to the nation. Naming the county-identity columns clears exactly those.
Same rule as §5 design rule 2 — enumerate rather than parse — applied to filter
context.

*Guard the blank numerator.* Without `NOT ISBLANK ( CountyRent )`, a county
with no rate in context returns `( BLANK() − median ) / median` = exactly
−100%, and non-blank values keep those counties in the visual's row set. A
Missouri-filtered table listed Iberia, Imperial and Iredell at −100%. Identical
in kind to the suspension-year bug in §8.5: a blank entering arithmetic and
leaving as a finding.

*Rank ties skip, they do not densify.* `County Rank in State` uses `RANKX` with
ties set to `Skip` — standard competition ranking, 1, 2, 2, 4 — rather than
`Dense`, which numbers distinct values instead of positions. This is not
cosmetic. Pastureland rates are published to the half dollar (§3.13), so ties
are routine: three Missouri counties sit at exactly $43.50 in 2025. `Skip`
reports Iron County as 40th of 106, `Dense` reports it as 21st, and 21st of 106
is a materially different claim to put in front of a producer. `Skip` is also
the only mode that makes the rank and `Counties Reporting in State`
interpretable as a pair.

`Counties Reporting` is deliberately *not* guarded on land category, unlike the
rate measures. A distinct count of counties is well defined across categories —
"how many counties published any rate" is a real question — where a mean across
categories is an artifact (§8.4). The guard exists to make wrong answers
unavailable, not to make every measure behave alike. The cost is that the
measure is non-additive: 2025 reads 622 + 2,317 + 1,825 across categories
against a 2,690 total, because most counties publish two. Correct, and worth a
tooltip in the report layer.

---

## 9. Forward design: ERS chile pepper fact

Not ingested. Designed for so the second star can attach without a refactor.

Source: USDA ERS *U.S. Bell and Chile Pepper Statistics* / Vegetables and
Pulses Yearbook (`vegetablespulsesyearbooktables.xlsx`).

Planned `fact_chile_pepper` grain: state × year × metric, joining on
`state_key` and `year_key`. The two conformed dimensions — `dim_state` and
`dim_year` — are what make a cross-fact visual legitimate rather than a lookup
hack.

Two constraints recorded now:

1. **Do not roll cash rent up to state inside the model** to match chile grain.
   Averaging county rates without acreage weights does not reproduce the state
   estimate NASS publishes. Use NASS's own state-level series if state rent is
   needed.
2. **Do not share a fact table across units.** $/acre, acres, tons, $/ton, and
   $1,000 of value do not share a grain or a unit.

The ERS workbook also carries trade-by-country and per-capita-use series at
country × year and national × year grain. Those need their own fact tables and
are out of scope for this version.

---

## 10. Open items

**10.1 — `estimation_method` cutover year. Resolved 2026-08-29.** Flagged for
verification because `crntqm25.pdf` contains two different cutover statements.
They refer to different geographic levels: state-level model-based estimates
were incorporated beginning with the 2022 estimate year, county-level
model-based estimates beginning with the 2021 estimate year. This extract is
county-level, so **2021 is correct** and `estimation_method` flips there. The
2021 cutover is independently corroborated by the data: CV publication and the
residual-grain change both start in 2021 (§3.4, §3.5). Carry the distinction
into the article's methodology section, because anyone checking the source
document will hit the same 2022 sentence.

**10.2 — CPI-U base year and partial-year averages.** Base is 2025, itself an
eleven-month average (§7.2). 2026 carries a seven-month average. Revisit when
BLS publishes the 2026 annual figure, and re-evaluate whether 2025 remains the
right base once a clean twelve-month year is available.

**10.3 — Suppression semantics.** The extract cannot distinguish disclosure
suppression from non-estimation (§3.6). If the article makes a claim about how
much data is withheld, that claim needs a caveat or a second source.

**10.4 — Hardcoded source path. Closed 2026-09-12.** `src_cash_rents` now
opens `SourceFolder` + `SourceFile` as Power Query parameters. The PBIX
refreshes on any machine where those two values are set.

**10.5 — Scope of the §3 profile figures.** Two errors in v0.1.x had the same
cause: figures computed across all 46,942 rows and quoted as if county-scoped
(§3.9, §3.10). Any figure in §3 states its scope; check it before quoting one
as an expected value for a filtered query.

---

**10.6 — Iron County 2025 pastureland is a low-confidence outlier. Open.**
The county's published series is 27.0, 20.0, 18.5, 16.0, 13.5, 16.5, **43.5**,
22.0 for 2019-2026: six years of decline, a 164% single-year jump, then most of
it given back. The CV moves with it — 7.0 in 2024, **28.7** in 2025, 8.3 in
2026. 28.7 is the highest CV in the county's series and sits in the `LOW`
confidence band (D4), so the jump is at least partly estimate noise rather than
a rent movement. Missouri's median CV that year is 5.85 (§8.2), so this is the
county, not the state.

The $43.50 figure was published to social media on 2026-09-14 as the headline
number, with its +9.4% against the state median and its rank of 40 of 106.
Those three figures are all arithmetically correct and all rest on the least
reliable observation in the series. **Withdrawn from the article pending
resolution.**

What this generalizes to, and the reason it is an open item rather than a
correction: nothing in the model stops a low-confidence estimate from being
used as a headline. `Low Confidence Share` and `Median CV` exist but describe
populations, and `Rent vs State Median` and `County Rank in State` are guarded
on blanks and land category but not on confidence. A single-county figure
quoted in the article must carry its CV, and a figure in the `LOW` band must
not carry a claim on its own. Whether that becomes a measure-level guard, a
report-layer conditional format, or a review rule is undecided.

The three candidate explanations — a changed respondent panel within the
county, a genuine local rent event, or a model-based estimate pulled by a
sparse sample — are not separable from this extract. The county panel is not
published, and §3.6 means a thin year and a suppressed year look identical.
Aaron's father operates in Iron County and is interviewed on 2026-09-19; **ask
him directly whether pasture rents there moved in 2025.** A producer's answer
does not settle a statistical question, but "nothing happened" versus "ground
got tight when the neighbors' lease came up" points at different halves of it,
and either is quotable.

**10.7 — Doña Ana irrigated cropland is not a chile rent proxy. Open.**
The 2026 irrigated figure of $296.00/acre, +78.9% above the NM median of
$165.50 across 10 counties (§8.2), is sound as a cash rents fact. The
interpretation attached to it is not: Doña Ana's irrigated acreage is dominated
by pecans, alfalfa and cotton, and all of New Mexico planted 8,200 acres of
chile in 2025 (`NM2025_Chile_Production03062026.pdf`) against a county irrigated
base many times that. Describing this rate as the cost of chile ground
overstates what the number knows.

Compounding it, Doña Ana chile acreage is withheld as `(D)` for both 2024 and
2025 in the NM county table, so the county-level rent figure cannot be paired
with a county-level chile figure at all. The defensible framing is Doña Ana as
expensive irrigated ground in the county that anchors New Mexico chile — a
setting, not a cost of production. Revisit at the ERS ingest (§9), which may
support a state-level chile-acreage-weighted framing that this county-level
pairing cannot.

## 11. Changelog

| Version | Date | Change |
|---|---|---|
| 0.3.4 | 2026-09-16 | **D14 ratified and executed** — `dim_year.survey_status` renamed to `cash_rents_survey_status`, so the conformed dimension no longer asserts a Cash Rents suspension over chile years that published normally. The rename surfaced three dependents, none of which the model reported on open: the `AddedResidualGrain` conditional column lost its first clause and returned errors on all 19 rows; that column's `type text` ascription was invalid against the nulls it returns by design and is now `type nullable text`; and `Rent YoY Pct (Matched Counties)` and `Rent YoY Pct (Unmatched)` both referenced the old column name and were broken in a file that opened without complaint. §6.5 records the conformed-dimension naming rule and the ascription. §8.5 updated to the new name. `chk_row_counts` extended to 19 assertions — suspension row count and `residual_grain` null count, both expecting 2 — because no existing assertion caught any of the three breaks. |
| 0.3.3 | 2026-09-14 | §10.6 added — Iron County 2025 pastureland ($43.50, CV 28.7) is a `LOW`-confidence outlier in a declining series; the figure and its derived rank and vs-median claims are withdrawn from the article, and the general gap is that no measure stops a low-confidence estimate becoming a headline. §10.7 added — Doña Ana irrigated rent is not a chile-ground proxy, and the county's chile acreage is `(D)` in 2024-2025 so the pairing is unavailable. §2.1 added — reader-facing explanation of why a 2026 rate exists before 2026 ends, required in every version of the article. §7.3 added — worked nominal-versus-real figures for Iron County and the endpoint-sensitivity rule that the existing real-dollar rule does not cover. §8.2 baseline extended with the Iron County series and `Counties Reporting in State` for MO pastureland 2026 (104). Header now records the repo URL rather than a suggested path. |
| 0.3.2 | 2026-09-13 | Four peer-context measures built and validated for the Iron County interview page: `State Median CV`, `State Median Rent per Acre`, `County Rank in State`, `Counties Reporting in State`. §8.2 baseline extended. §8.6 adds the `Skip`-versus-`Dense` rank tie rule. **§8.2 corrected** — the rising-CV finding was stated as holding "from 2021 to 2026" on figures that are 2021 and 2025; 2026 turns down on every national measure and Missouri is not monotonic. §3.2 adds the statutory cause of the 2015 and 2018 skips; §3.3 adds the Iron County 2008 case and the rule that a coverage gap and a suspension gap must be annotated separately. |
| 0.1.0 | 2026-08-29 | Initial specification. Profile complete, decisions D1–D10 resolved, open item 10.1 resolved. No transformations executed. |
| 0.1.1 | 2026-08-29 | §2 corrected: extract filename carries GUID hyphenation on disk (`9A9F55D7-E267-38C6-ACB9-DF106291B5A7.csv`). Same 32 hex characters, same extract. No model impact. |
| 0.1.2 | 2026-09-04 | §3.9 corrected: 408 of 1,719 real county names are reused across states. The prior 410/1,721 counted `OTHER COUNTIES` and `OTHER (COMBINED) COUNTIES` as county names. Caught during the step 8 branch validation. |
| 0.3.0 | 2026-09-05 | Five measures built. §8 expanded into conventions, measures-as-built, and the reasoning behind three naming and behaviour decisions. §8.5 records the composition-versus-rate finding and the −100% suspension-year bug. §6.5 adds the **Show items with no data** build requirement — the 19-row calendar is necessary but not sufficient to make the break visible. |
| 0.3.1 | 2026-09-12 | Remaining four measures built and validated; §8.2 expanded with a regression baseline and the rising-CV finding. §8.6 added — peer groups iterate the fact rather than the dimension, `REMOVEFILTERS` on named columns rather than `ALLEXCEPT` on the table, and the blank-numerator guard. §3.14 scoped: county-scoped pastureland figures are 383/75 against the whole-extract 406/84. 10.4 closed. |
| 0.2.0 | 2026-09-04 | Reshape built and validated; spec reconciled to the pipeline as constructed. §5 rewritten (two step reorderings, design rules, `chk_row_counts`). §3.8 rewritten — the comma hazard is a locale-dependent misparse, not a silent null. §6.4 `is_chile_producing` deferred. §6.5 `residual_grain` null on suspension years; date-table limitation noted. §6.7 single-direction rationale. §7 CPI series loaded, with 2025 as an eleven-month and 2026 as a seven-month average. Open items 10.4, 10.5 added. |
| 0.1.3 | 2026-09-04 | §3.10 corrected: real counties carry 23 district codes / 83 names / 306 state-district pairs. The prior 24/84 counted sentinel code `99` from the state-residual rows. Caught during `dim_geography` validation. Both §3.9 and §3.10 errors had the same cause — profile figures computed over the full extract and quoted as if county-scoped. |
