# USDA NASS County Cash Rents — Data Model Specification

**Version:** 0.5.0
**Status:** Three facts built and validated — county cash rents, state chile, and
chile Census of Agriculture. Thirteen measures written and validated, all on the
cash rents star; no chile measures yet. Two report pages built for the 2026-09-19
producer interview; report layer proper not started. `chk_row_counts` at 35
assertions, all passing. §10.6 resolved.
**Last updated:** 2026-09-17
**Owner:** Aaron / Heat & Harvest Data Desk
**Repo:** `github.com/chaferoc/american-chile-economy`, at
`docs/data-model/cash-rents-data-model.md`

---

## 1. Purpose and scope

This document specifies the dimensional model for the USDA NASS county-level
cash rents series as loaded into the "American Chile Economy" Power BI report.
It is the authoritative record of grain, keys, naming, and modeling decisions,
and is written to be quotable in the article's methodology section.

Scope of this version: the county cash rents fact and its dimensions (§3–§8),
and the NASS state-level chile fact (§9). The ERS national chile series is
profiled but not ingested; see §9.9.

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

D1–D10 taken 2026-08-29; D11–D15 during the builds they govern. Change requires
a version bump and an entry in §11.

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
| D11 | `fact_chile_state` shape | **Wide.** One row per state × year with six typed measure columns plus one `suppression_code`. Taken 2026-09-16. A metric dimension would need one value column carrying five different units, which §9.1 forbids. |
| D12 | `OTHER STATES` chile rows | **Separate table.** `fact_chile_state_residual`, per D2's precedent. Four rows. |
| D13 | `is_chile_producing` | **Delete, do not build.** §6.4 deferred it to this ingest. The producing set moves — Arizona left after 2018, Ohio arrived in 2024 — so a boolean freezes something that changes, and the fact's own contents answer the question. |
| D14 | `dim_year` survey status naming | **Rename to `cash_rents_survey_status`.** Taken 2026-09-16. Not moved onto the fact: suspension is a property of a year, not of a rent observation, and moving it would repeat the value 79,064 times. See §6.5. |
| D15 | Chile utilization family | **Deferred.** Fresh market, processing, not sold and utilized production are out of scope for v0.4.0; see §9.8. |
| D16 | Census of Agriculture chile rows | **Build as a third fact.** `fact_chile_census`, state × census year, from the Census rows already present in the acres-harvested extract. Taken 2026-09-17. It is the only chile source with complete 50-state coverage, and it is the documented input to the program review that decides survey coverage (§9.12) — the one series not shaped by the decisions the article is about. See §9.11. |
| D17 | `dim_state` grain | **Widen from the cash rents state list to the union of states appearing in any fact.** Taken 2026-09-17. Was 49. Alaska has no cash rents rows but does have a Census chile row; cutting it would have dropped a state to fit a dimension built for a different survey, which is the error §9.11 exists to avoid. 50 rows. Alaska shows blank rents in a state slicer — correct, and the price of the dimension meaning what its name says. |
| D18 | Low-confidence figures | **Publish with the CV attached. Never suppress.** Taken 2026-09-17, resolving §10.6. A measure-level guard that blanked `LOW`-band values would hide the observation the reporting exists to investigate, and would put blanks back into arithmetic — the §8.5 and §8.6 failure mode. Enforced as a review rule and report-layer conditional formatting, not as a measure. |

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

**Load-validation targets.** Implemented as the `chk_row_counts` query, 28
assertions covering row counts, both fact/residual reconciliations, CV
population, referential integrity on all nine foreign keys, `dim_geography`
and `fact_chile_state` key uniqueness, the two `dim_year` suspension-year
invariants (D14), and the chile suppression count (§9.4). All 28 pass as of
2026-09-16.

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

Grain: one state. **50 rows** (D17). Conformed dimension shared with all three
facts.

Rows are sourced from `src_cash_rents`, which covers 49 states — NASS does not
run the Cash Rents Survey in Alaska. Alaska is appended as a literal after the
key padding step, then joined to the attribute table like any other row. The
dimension's grain is therefore the union of states appearing in any fact, not
the cash rents state list; see D17 for why that distinction is load-bearing.

| Column | Type | Note |
|---|---|---|
| `state_key` | text(2) | PK, state ANSI/FIPS |
| `state_name` | text | |
| `state_abbr` | text(2) | |
| `nass_region` | text | Northeast, Lake, Corn Belt, Northern Plains, Appalachian, Southeast, Delta, Southern Plains, Mountain, Pacific — per `crntqm25.pdf`. Alaska is `Pacific`, following Hawaii. |

`is_chile_producing` was deferred here in v0.2.0 and **deleted under D13** rather
than built. The producing set moves, so a boolean freezes something that changes.

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

## 9. Chile pepper facts

Built and validated 2026-09-16. This section replaces the forward design
written 2026-08-29, which was wrong in one structural way: it planned a single
`fact_chile_pepper` at state × year sourced from the ERS yearbook. The yearbook
has no state dimension, so the conformed `dim_state` join it was designed
around could not be satisfied from that source at all.

### 9.1 Two facts, not one

| Fact | Grain | Source | Status |
|---|---|---|---|
| `fact_chile_state` | state × year | NASS Quick Stats, five extracts | built, 62 rows |
| `fact_chile_state_residual` | `OTHER STATES` × year | same | built, 4 rows |
| `fact_chile_census` | state × census year | NASS Quick Stats, Census program | built, 150 rows |
| `fact_chile_national` | year | ERS Yearbook Table 54 | not built, §9.9 |

The state and national series are not unionable. National is farm-weight
million pounds with imports, exports and per-capita availability; state is
acres, cwt and dollars. They answer different questions and share only
`dim_year`.

Two constraints carried forward from the original §9, both still binding:

1. **Do not roll cash rent up to state inside the model** to match chile grain.
   Averaging county rates without acreage weights does not reproduce the state
   estimate NASS publishes. Use NASS's own state-level series if state rent is
   needed.
2. **Do not share a fact table across units.** $/acre, acres, tons, $/ton and
   $1,000 of value do not share a grain or a unit.

### 9.2 Source extracts

Five Quick Stats CSVs, Commodity = PEPPERS, Geographic Level = State, 2008–2025.
Quick Stats exports one data item per download, which is why there are five.
All five live in `data/raw` alongside the cash rents extract and resolve through
the existing `SourceFolder` parameter; filenames are literals in each staging
query rather than parameters, because the filename is the query's identity.

| Metric | File (GUID) | Raw rows | Filtered |
|---|---|---|---|
| `acres_planted` | `6B8B4B57-A8C4-30E9-BBF9-F8D1EDEEBF18` | 65 | 65 |
| `acres_harvested` | `CA70B843-82A4-3C22-BE2A-CF7C9BB6B0CC` | 289 | 66 |
| `yield_cwt_per_acre` | `3CDFA62B-373A-3A19-95CB-881207B34387` | 65 | 65 |
| `price_usd_per_cwt` | `76EDE65A-E714-3F81-AF90-358FED3B79C2` | 81 | 65 |
| `production_cwt` | `0909B7A5-83F4-37C2-8555-C2F43EF0D893` | 1,880 | 65 |
| `production_value_usd` | same file | 1,880 | 65 |

A sixth download, `CA70B843-…__1_.csv`, is a byte-identical duplicate of the
acres-harvested file and is discarded.

### 9.3 Ingest filters are per-extract, and the third one is the trap

Four filters take each extract to one clean row per state × year:

1. `Program = SURVEY` — drops Census of Agriculture rows.
2. `Domain = TOTAL` — drops `ORGANIC STATUS` breakdowns.
3. `Period` — **`MARKETING YEAR` for price, `YEAR` for everything else.**
4. `Data Item` — the exact item string.

Each filter does real work in exactly one extract and is a no-op in the others.
`SURVEY` takes acres harvested from 289 rows to 66. `MARKETING YEAR` takes price
from 81 to 65. `Data Item` takes production from 1,068 to 65. The others pass
through untouched *today*, and they stay in the query anyway: a re-pull that
starts returning Census rows or organic breakdowns then fails loudly instead of
silently doubling a series.

The price trap is the one that would corrupt quietly. Price publishes under both
periods — 65 rows at `MARKETING YEAR` covering the whole range, plus a 16-row
`YEAR` series for 2008–2011 only, with different values. New Mexico 2011 is
$33.90 marketing-year against $23.00 year. Taking both yields 16 duplicate
state-years; taking the wrong one yields a short series that looks fine.
**Check value: New Mexico 2011 price must read 33.9.**

`Period` was a dead column in the cash rents extract, dropped at ingest under
§3.1. Here it is a filter key. The dead-column list is a property of one
extract, not of Quick Stats — re-profile per source rather than reusing §3.1.

**Filter 1 is now load-bearing in a second sense.** `Program` was written as a
guard against Census rows contaminating a survey series. Since D16 it is also
the switch that selects between two facts built from the same file:
`src_chile_acres_harvested` filters `SURVEY` and feeds `fact_chile_state`;
`fact_chile_census` filters `CENSUS` and stands alone (§9.11). Neither is a
subset of the other and they must never be unioned — different populations,
different instruments, different collection years.

### 9.4 Suppression is explicit, whole-row, and padded

Unlike the cash rents extract, where all suppression collapsed to blank and the
distinction was unrecoverable (§3.6), Quick Stats writes `(D)` and `(Z)` as
literal strings in `Value`. `(D)` is withheld for disclosure; `(Z)` is a value
below half the publication unit. The distinction survives and the model keeps
it.

**Suppression is whole-row.** Five state-years carry `(D)`, and in every one of
them all six metrics are withheld together: California 2025, Ohio 2024 and 2025,
Texas 2024 and 2025. No state-year has some metrics withheld and others
published. This is what makes D11's wide shape clean — one `suppression_code`
column on the row covers every case in the data, rather than six paired columns.
It is a property of the current extract, not a guarantee, which is why
`chk_row_counts` asserts the count of 5.

**The suppression strings carry a leading space.** The raw value is `" (D)"`,
not `"(D)"`. This is a distinct hazard from §3.8's commas and it fails
differently: a comma makes `Number.FromText` misparse under some locales, where
the leading space makes an equality test silently not match, so both the
suppression detection and the cast guard fall through and the row errors. It is
handled once, by a `Text.Trim` on `value_raw` in `src_chile_state` after the
append, so every column and any future extract inherits it.

**`(Z)` occurs, and only in the Census rows.** The survey extracts contain no
`(Z)` at all, so v0.4.0 recorded the distinction without exercising it.
`fact_chile_census` carries one: a 2017 state row whose harvested acreage is
below half the publication unit. It is a real, very small observation, not a
withholding, and classifying it as missing would silently drop a state. Both
codes are trimmed and classified in the same expression; see §9.11.

### 9.5 Pipeline as built

Six staging queries, none loaded to the model. Each reads one file, applies its
four filters, removes the seventeen dead columns by name, stamps a `metric`
literal, and renames to `year_key` / `state_name` / `state_key` / `value_raw`.

`value_raw` stays text through staging. Casting at the source would destroy the
`(D)` / `(Z)` distinction §9.4 exists to preserve.

`metric` is stamped as a literal per query rather than parsed from the
`Data Item` string at pivot time. Same rule as §5 design rule 2: enumerate
rather than parse.

The seventeen dead columns are dropped with `Table.RemoveColumns` naming them,
not `Table.SelectColumns` naming the four survivors, so a new NASS column
arrives visible instead of vanishing. §5 design rule 1.

**Staging query `src_chile_state`** (not loaded):

| # | Step | Rows out |
|---|---|---|
| 1 | `Table.Combine` of the six staging queries | 391 |
| 2 | `Text.Trim` on `value_raw` (§9.4) | 391 |
| 3 | `Table.Pivot` on `metric`, no aggregation | **66** |

Pivot takes no aggregation function, so a duplicate state × year × metric
becomes an Error rather than a silent sum — same choice as step 7 of
`src_cash_rents`.

**Branch queries**, each a reference to `src_chile_state`:

| Query | Filter | Rows |
|---|---|---|
| `fact_chile_state` | `state_key` present — structural | 62 |
| `fact_chile_state_residual` | `state_name = "OTHER STATES"` — by label | 4 |

The two filters use different tests deliberately. A row NASS labels some third
way lands in neither stream and the 62 + 4 = 66 reconciliation stops balancing,
rather than one filter quietly claiming it.

Each branch then derives `suppression_code` from the six raw values, and casts
the six columns with commas stripped and `en-US` passed to `Number.FromText`
explicitly rather than relying on ambient culture — §3.8's hazard, handled the
same way here.

### 9.6 `fact_chile_state`

Grain: one state × year. 62 rows, five states.

| Column | Type | Null | Note |
|---|---|---|---|
| `state_key` | text(2) | no | FK → `dim_state`, zero-padded FIPS |
| `year_key` | int | no | FK → `dim_year` |
| `state_name` | text | no | carried for readability |
| `acres_planted` | nullable number | yes | null where withheld |
| `acres_harvested` | nullable number | yes | |
| `yield_cwt_per_acre` | nullable number | yes | |
| `price_usd_per_cwt` | nullable number | yes | marketing year (§9.3) |
| `production_cwt` | nullable number | yes | |
| `production_value_usd` | nullable number | yes | |
| `suppression_code` | nullable text | yes | `D`, `Z`, or null. 5 rows carry `D` |

Every measure column is `nullable number` and `suppression_code` is
`nullable text`. Ascribing a non-nullable type to a column that returns null by
design is the defect `residual_grain` carried from its first build until
2026-09-16; it does not fail at write time, only when something forces a real
evaluation.

The Quick Stats state codes match `dim_state[state_key]` without repair — both
are zero-padded two-character FIPS. `orphan state_key (chile fact)` asserts it.
This is what D6's decision to build `dim_state` as a conformed dimension before
the chile ingest bought.

**Coverage is intermittent, and that is not production ending.**

| State | Years published |
|---|---|
| New Mexico | 2008–2025, all 18 |
| California | 2008–2025, all 18 |
| Arizona | 2008–2018 only |
| Texas | 2008–2018, then 2024–2025 |
| Ohio | 2024–2025 only |

Arizona and Texas going quiet after 2018 is publication intermittency, the same
hazard as §3.12. A line chart renders it as a collapse to zero unless handled.
D13 deletes `is_chile_producing` for exactly this reason: the producing set
moves, so a boolean freezes a moment.

### 9.7 `fact_chile_state_residual`

Grain: one `OTHER STATES` × year. 4 rows — 2016, 2020, 2024, 2025.

No `state_key`. The column is blank on every residual row and is dropped rather
than carried as an empty string, because a blank that looks like a key is the
§3.6 trap. The residual joins `dim_year` only, which is one relationship where
`fact_cash_rent_residual` has two.

**Two of the four rows are published zeros, not withholdings.** 2016 reads 0 on
all six metrics; 2020 reads 0 on acres harvested and null on the other five.
`suppression_code` is null on both, correctly — a zero is a published estimate
and a null is an absence, and the model must not let them share a token.

The 2016 row therefore carries a price of $0.00/cwt and a yield of 0 cwt/acre.
Neither is a rate. They are the arithmetic shadow of zero acres, and any measure
that averages price or yield must exclude the residual. D12's table separation
enforces this structurally rather than by convention, which is the same argument
D2 made for cash rents.

### 9.8 Deferred: the utilization family (D15)

The production extract carries eight `PEPPERS, CHILE` items, not two. Six are a
utilization family and are out of scope for v0.4.0:

| Item family | Years | Rows | Withheld |
|---|---|---|---|
| Core production (cwt, $) | 2008–2025 | 65 each | 5 of 65 |
| Fresh market (cwt, $) | 2016–2025 | 38 each | 16 of 38 |
| Processing (tons, $) | 2016–2025 | 38 each | 16 of 38 |
| Not sold, utilized (cwt) | 2016–2025 | 33 each | 5 of 33 |

The family starts in 2016, is 42% withheld on four of its six items, and
introduces tons as a third unit. It answers the fresh-versus-processing
question, which is a live article angle, but at a different grain and coverage
from the core six. It gets its own fact when it is taken, not a widening of this
one.

### 9.9 Not built: `fact_chile_national`

Source: ERS *U.S. Bell and Chile Pepper Statistics* / Vegetables and Pulses
Yearbook (`vegetablespulsesyearbooktables.xlsx`), sheet
`Table 54-Chili Peppers, Pr`. 58 rows, 1980–2025, national only. Table 32 is the
bell series.

Three things to settle before it is built, all recorded during profiling:

1. **Table 54 has three series breaks and a preliminary year**, all footnoted on
   the sheet: production source changes from ERS to NASS estimates in 2018; the
   price source changes after 1999; the dry-basis conversion factor changes from
   5.0 to 8.0 in 1988; 2025 is flagged preliminary. These need explicit flags in
   the same spirit as `dim_year.cash_rents_survey_status` — not silent joins
   across a definition change.
2. **ERS deflates with a different index.** Table 54's constant-dollar column
   uses the GDP implicit price deflator at 2017=100; this model uses CPI-U at
   2025=100 (§7). Two real-dollar bases in one report is a footgun. Drop the ERS
   column and deflate the nominal price with the existing `deflator_to_base`.
3. The workbook also carries trade-by-country and per-capita-use series at
   country × year and national × year grain. Those are further facts again, not
   columns on this one.

### 9.10 Relationships

```
dim_state (1) ──< (*) fact_chile_state
dim_year  (1) ──< (*) fact_chile_state
dim_year  (1) ──< (*) fact_chile_state_residual
dim_state (1) ──< (*) fact_chile_census
dim_year  (1) ──< (*) fact_chile_census
```

Five relationships, all one-to-many, all single cross-filter direction, all
active. **Twelve in the file total.** Single direction matters more here than it
did in §6.7, not less: `dim_year` now reaches five fact tables, so a single
bidirectional edge would let a slicer on one fact reshape four others.

`dim_year` reaching `fact_chile_census` has a consequence worth stating rather
than discovering. That fact exists on three years only. A year slicer set to
anything else empties it, and any visual placing census beside survey is putting
a three-point series next to an eighteen-point one. Correct on a map page,
a trap on a line chart.

### 9.11 `fact_chile_census`

Grain: one state × census year. 150 rows — 50 states × 2012, 2017, 2022.
Built 2026-09-17 under D16.

| Column | Type | Null | Note |
|---|---|---|---|
| `year_key` | int | no | FK → `dim_year`. **Whole number, not text** — see below |
| `state_key` | text(2) | no | FK → `dim_state`, state ANSI, zero-padded |
| `state_name` | text | no | |
| `acres_harvested` | int | yes | Null where `suppression_code` is set |
| `suppression_code` | text | yes | `D`, `Z`, or null |

**Why it exists.** Every other chile series in this model is shaped by the thing
the article is about. The survey covers whichever states NASS currently
estimates, so a national total built from it moves when the program moves, not
only when the crop does. The Census covers all fifty states on a fixed
five-year cadence and is the documented input to the review that sets survey
coverage (§9.12). It is the only chile series here that can carry a national
claim without the claim being partly about NASS's budget.

**Construction.** A duplicate of `src_chile_acres_harvested` with
`FilterProgram` repointed from `SURVEY` to `CENSUS`, loaded rather than staged.
The other three filters are unchanged and all do work: `Domain = TOTAL` drops 73
rows of `AREA HARVESTED, FRESH MARKET & PROCESSING` and `OPERATORS` breakdowns,
taking 223 Census rows to 150. Then two added columns:

```
suppression_code = let v = Text.Trim([value_raw]) in
                   if v = "(D)" then "D" else if v = "(Z)" then "Z" else null

acres_harvested  = if [suppression_code] = null
                   then Number.FromText(Text.Remove(Text.Trim([value_raw]), {","}))
                   else null
```

Both §3.8's commas and §9.4's leading space are handled in the one expression.
Fifteen of the 150 values carry a thousands separator, so omitting
`Text.Remove` fails on exactly the largest states — the rows any check value
would be computed from.

**`year_key` must be typed.** It was left as text on first build. `dim_year`
carries a whole number, the relationship was created without complaint, and the
orphan assertion reported all three distinct years as orphans because
`List.Difference` compares text to number and never matches. The relationship
was inert. The survey path does not have this problem because
`fact_chile_state` types `year_key` downstream of staging; the duplicate never
inherited that step. **`state_key` stays text** — it matches `dim_state` as
text, and converting it would strip the leading zeros.

**Assertions** (`chk_row_counts`, five of the seven added at this version):

| Check | Expected |
|---|---|
| `fact_chile_census` | 150 |
| census acres present | 136 |
| census suppression (D) | 13 |
| census suppression (Z) | 1 |
| census fact key uniqueness | 0 |
| orphan `year_key` (chile census) | 0 |
| orphan `state_key` (chile census) | 0 |

The `(D)` rows are nine in 2012 and four in 2017. **2022 is complete** — all
fifty states numeric. This asymmetry is useful rather than annoying: the 2012
total is understated by nine withheld states, so a 2012→2022 decline computed
off published values is a *floor*, not a point estimate. Sum of published acres
is 31,265 (2012), 23,423 (2017), 23,122 (2022) — at least a 26% fall, with the
bias running in the safe direction for the claim.

**Not a continuation of the survey series.** The Census enumerates all farms;
the survey samples commercial operations in selected states. Arizona reads 1,100
harvested acres in the 2018 survey and 386 in the 2022 Census. Those are not two
points on one line and must never be charted as one.

### 9.12 Coverage is a decision, and it is documented

The single most useful finding in the chile work, and the one the article turns
on. It is recorded here because it constrains what the model may claim, not just
what the article says.

Arizona and Texas stop appearing in the chile survey after 2018. Texas and Ohio
appear in 2024–2025 as `(D)`. Neither is a data quality problem, and neither is
a statement about the crop.

- **NASS Program Review, Vegetable Program, March 2019.** Effective with the
  2019 crop, chile's estimating states become California and New Mexico;
  Arizona and Texas are removed. The stated method: for each crop, states are
  arrayed by production and value of production, largest first, and the states
  accounting for the largest proportion are retained, given limited resources.
- **Not specific to chile.** The same review removed eight states from tomatoes
  and eight from sweet corn, left lima beans with no estimating states at all,
  and discontinued every in-season vegetable forecast.
- **NASS Program Review, Vegetable Program, April 2024.** Chile's estimating
  states become California, New Mexico, Ohio and Texas, with none removed.
  Texas is restored and Ohio added. Arizona is not restored.
- **The primary input to that ranking is the Census of Agriculture** — which is
  what makes `fact_chile_census` more than a supplementary series. It is the
  table the coverage decision is made from.

Census harvested acres, from `fact_chile_census`:

| State | 2012 | 2017 | 2022 |
|---|---|---|---|
| New Mexico | 9,577 | 8,313 | 8,484 |
| California | 7,029 | 4,168 | 3,257 |
| Texas | 4,288 | 2,074 | 2,249 |
| Florida | 1,188 | 590 | 1,371 |
| Ohio | 698 | 873 | 1,058 |
| Arizona | 1,944 | 1,250 | 386 |

Texas ranked third in 2022 and returned. Ohio ranked fifth and was added.
Arizona had fallen to eleventh, down 80% in a decade, and stayed out. The
ranking above is harvested acres; the review weighs production and value, so
this is a proxy for their ordering, not their ordering — see §10.9.

**What this forbids.** A New Mexico share-of-U.S. measure built on
`fact_chile_state` is not defensible at any grain. The denominator is whichever
states NASS estimated that year — four through 2018, two from 2019, plus a
residual present in four of eighteen years and reading zero in two of them. New
Mexico's share climbs from roughly 55% to near 100% across the series, and most
of that climb is Arizona and Texas leaving the program. It would be the most
quotable number in the report and the least true. Not built, by decision.

**What it permits.** New Mexico in absolute acres and production from the
survey, the fifty-state picture from the Census, and the national supply series
from ERS (§9.9) — three sources, each used where it is whole, with the reader
doing the comparison.

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

**10.6 — Iron County 2025 pastureland is a low-confidence outlier. Resolved
2026-09-17 (D18); the interview question remains open.**
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
reliable observation in the series.

What this generalizes to, and the reason it is an open item rather than a
correction: nothing in the model stops a low-confidence estimate from being
used as a headline. `Low Confidence Share` and `Median CV` exist but describe
populations, and `Rent vs State Median` and `County Rank in State` are guarded
on blanks and land category but not on confidence. A single-county figure
quoted in the article must carry its CV, and a figure in the `LOW` band must
not carry a claim on its own.

**Resolved as a review rule, not a measure (D18).** A measure-level guard that
blanked `LOW`-band values was considered and rejected. It would suppress exactly
the observation the reporting exists to investigate, and it would reintroduce
blanks into arithmetic — the failure mode already hit twice, in §8.5 and §8.6.
Transparency is also the better answer to the source: the figure was published
where the interview subject could see it, and the article that follows owes him
an explanation of the variance rather than its disappearance.

So: the $43.50 is not withdrawn. It is published with its CV beside it, and the
rank and vs-median figures are used as elicitation on the §5 one-pager rather
than as standalone claims in the article. Enforcement is a `Confidence Band`
display measure driving report-layer conditional formatting, plus the review
rule above. Neither blanks a value.

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

**10.8 — Chile measures and the intermittency problem. Open.**
No DAX measures exist on either chile fact. Before any chile visual is built,
the measures need an answer to a problem the cash rents star does not have:
a gap in a state series has three causes and they render identically. A state
may be *out of program* and have no row at all (Arizona 2019–2025, Texas
2019–2023); *estimated but withheld*, with a row and a `(D)` (California 2025,
Texas and Ohio 2024–2025); or carry a *published zero* (the residual in 2016 and
2020, §9.7). Only the second is a gap in the data. A line chart renders all three
as a fall to zero.

The measure-level half is a rule: never let an absent row become 0. Blank is the
correct return and Power BI gaps it correctly on a categorical axis, so the risk
is `DIVIDE` defaults, `COALESCE`, and any `+ 0`. The report-level half is that
Arizona and Texas lines must **end** in 2018 rather than continue — a series
that stops is honest, a series that falls to zero is not. A `Chile Coverage
Status` measure returning Published / Withheld / Not estimated serves tooltips
and annotation, not filtering.

Yield and price should be derived rather than aggregated: production cwt ÷
acres harvested, and production $ ÷ production cwt. Tested against all 59
state-years with the inputs published, median error 0.07% and worst case 1.2%.
That reproduces the published rate at single-state grain and stays correct when
several states are in context, which averaging the published rates does not.

**10.9 — Why Florida was not added in 2024. Open, and possibly unanswerable.**
Florida ranked fourth in the 2022 Census at 1,371 harvested acres, above Ohio's
1,058, and was not added to the chile estimating program by the April 2024
review (§9.12). The review weighs production and value of production, not
harvested acres, so the discrepancy may simply be that Florida's chile is lower
value, or that its pepper acreage is captured under Peppers, Bell. **Do not
assert a reason in the article.** Either find the value-based ranking the review
used, or state the ranking as acres and leave the Florida case unexplained.

**10.10 — New Mexico Chile Survey consolidation. Open, unsourced.**
A 2022 OMB supporting statement reportedly records the standalone New Mexico
Chile Survey being folded into the End of Season Vegetable Survey. Only a
secondary mirror has been located. This is a change to the instrument itself and
would be a strong detail for the article, but it does not go in until the
Federal Register or reginfo.gov entry is found.

---

## 11. Changelog

| Version | Date | Change |
|---|---|---|
| 0.5.0 | 2026-09-17 | **Third fact built.** `fact_chile_census` (150 rows, 50 states × 2012/2017/2022) from the Census of Agriculture rows already present in the acres-harvested extract, joined to `dim_state` and `dim_year`; twelve relationships in the file. D16, D17, D18 taken. **`dim_state` widened 49 → 50** — its grain was the cash rents survey's state list, and Alaska has a Census chile row but no rent rows; the dimension now means the union of states in any fact (D17). §6.4 also drops the `is_chile_producing` row, deleted under D13 in 0.4.0 but left standing in the table. **§10.6 resolved (D18):** low-confidence figures publish with their CV attached rather than being suppressed — a measure-level guard would hide the observation the reporting exists to investigate and would put blanks back into arithmetic (§8.5, §8.6); the Iron County $43.50 is no longer withdrawn. **§9.12 added** — coverage is a documented decision: the March 2019 program review removed Arizona and Texas from the chile estimating program by ranking states on production and value, the April 2024 review restored Texas and added Ohio, and the Census of Agriculture is the ranking's primary input. This forbids a New Mexico share-of-U.S. measure, whose denominator moves with the program rather than the crop. Two failures recorded: `year_key` left as text made a relationship inert while the orphan check read all three years as orphans, and `(Z)` — recorded in 0.4.0 but never exercised — occurs once in the Census rows and would have been dropped as missing. `chk_row_counts` extended to 35 assertions. §9.3 records that the `Program` filter now selects between two facts rather than only guarding one. Open items 10.8 (chile measures and the three causes of a gap), 10.9 (Florida's absence from the 2024 review), 10.10 (NM Chile Survey consolidation, unsourced) added. |
| 0.4.0 | 2026-09-16 | **Second star built.** `fact_chile_state` (62 rows) and `fact_chile_state_residual` (4 rows) ingested from five NASS Quick Stats extracts, joined to the conformed `dim_state` and `dim_year` with no key repair needed. **§9 rewritten from forward design to as-built** — the 2026-08-29 design was structurally wrong, planning one state-level fact sourced from the ERS yearbook, which has no state dimension; state chile comes from Quick Stats and the ERS national series becomes a separate unbuilt fact (§9.9). D11 (wide shape, six measure columns plus `suppression_code`), D12 (`OTHER STATES` to its own table), D13 (`is_chile_producing` deleted rather than built) and D15 (utilization family deferred) ratified. Three findings recorded: suppression is whole-row and coincident across all six metrics, which is what makes the wide shape clean (§9.4); `(D)` strings carry a leading space, a hazard distinct from §3.8's commas because it fails as a silent non-match rather than a misparse (§9.4); the residual's 2016 and 2020 rows are published zeros, so its price and yield are not rates and it must be excluded from any average (§9.7). `chk_row_counts` extended to 28 assertions. `chile-ingest-profile.md` folded in and retired. |
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
