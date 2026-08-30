# USDA NASS County Cash Rents — Data Model Specification

**Version:** 0.1.0
**Status:** Approved for build. No transformations executed yet.
**Last updated:** 2026-08-29
**Owner:** Aaron / Heat & Harvest Data Desk
**Suggested repo path:** `docs/data-model/cash-rents-data-model.md`

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
| Extract | Quick Stats CSV, `9A9F55D7E26738C6ACB9DF106291B5A7.csv` |
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

---

## 3. Profile findings that constrain the model

These are the source characteristics the model is built around. Established by
profiling the extract on 2026-08-29.

**3.1 Dead columns.** Eleven of 24 columns carry no information and are dropped
at ingest: `Program`, `Period`, `Geo Level`, `Commodity`, `Domain`,
`Domain Category`, `watershed_code` (all single-valued), and `Week Ending`,
`Zip Code`, `Region`, `Watershed` (all empty).

**3.2 Two survey suspensions.** 2015 and 2018 are absent. These are NASS
program-wide suspensions, not extract gaps. 17 of 19 years in range are
present.

**3.3 2008 is a partial year.** 1,592 rows against ~2,890 from 2009 onward, and
135 irrigated values against ~800. Not a comparable baseline; exclude from
trend claims or annotate.

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
Commas must be stripped before type conversion or those rows null out silently.
They are the highest-value irrigated counties, so the loss would be systematic.

**3.9 County ANSI is not a key.** It holds only 265 distinct values — the
3-digit within-state FIPS. County *name* is not a key either: 410 of 1,721
names appear in more than one state. `State ANSI` + `County ANSI` yields 2,938
distinct entities and is unique within year (zero duplicates).

**3.10 Ag district code is only unique within state.** 24 codes cover 84
district names across 306 state × district pairs.

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
| D3 | `dim_year` rows | **19 rows**, 2008–2026 inclusive, with explicit `SUSPENDED` status for 2015 and 2018. Visuals break the line at suspension years. |
| D4 | CV placement | **Column on the fact.** Plus derived `confidence_band`: `HIGH` <10%, `MODERATE` 10–20%, `LOW` >20%. |
| D5 | `geo_key` format | **5-character text FIPS** (`state_ansi` + `county_ansi`, zero-padded). Joins natively to Power BI maps, TIGER shapefiles, and other USDA county datasets. |
| D6 | `dim_state` | **Build now**, before the ERS ingest, as a conformed dimension. |
| D7 | Currency | **Both.** Nominal stored on the fact; real dollars derived in DAX from a CPI-U deflator on `dim_year`. See §7. |
| D8 | Non-standard entities | **Keep as-is.** No normalization of Hawaii merged entities. |
| D9 | Load scope | **All 49 states**, full 2008–2026 range. |
| D10 | Artifact location | **Versioned markdown in a git repo**, current copy kept in the H&H Data Desk project. |

---

## 5. Reshape specification

Target grain: one row per geography × year × land category, where a value was
published.

1. Drop the eleven dead columns (§3.1).
2. Strip thousands separators from the irrigated value column (§3.8).
3. Cast all six measure columns to decimal. Blank becomes null. Null is never
   coalesced to zero at any point in the pipeline.
4. Unpivot all six measure columns to attribute/value pairs.
5. Split the attribute into `land_category` and `metric` (`VALUE` or `CV`).
6. Pivot `metric` back so each row carries `rent_usd_per_acre` and `cv_pct`.
7. Route rows by county name into three streams: true counties,
   `OTHER (COMBINED) COUNTIES` (district residual), `OTHER COUNTIES` (state
   residual).
8. Drop rows where `rent_usd_per_acre` is null (D1).

Expected output volumes:

| Stream | Rows |
|---|---|
| `fact_cash_rent` (counties) | 79,064 |
| `fact_cash_rent_residual` — district level | 5,562 |
| `fact_cash_rent_residual` — state level | 787 |
| **Total populated combinations** | **85,413** |
| (Dense grid, for reference — not loaded) | 140,826 |

These counts are the load-validation targets. A Power Query refresh that
returns anything else has lost or duplicated rows.

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
| `is_chile_producing` | bool | set at ERS ingest |

### 6.5 `dim_year`

Grain: one calendar year. 19 rows, 2008–2026 inclusive.

| Column | Type | Note |
|---|---|---|
| `year_key` | int | PK |
| `year` | int | |
| `survey_status` | text | `PUBLISHED`; `SUSPENDED` for 2015 and 2018 |
| `is_partial_coverage` | bool | true for 2008 only (§3.3) |
| `cv_published` | bool | false ≤2020, true ≥2021 |
| `estimation_method` | text | `SURVEY_DIRECT_EXPANSION` ≤2020, `BAYESIAN_SMALL_AREA` ≥2021 — see §10.1 |
| `residual_grain` | text | `NONE` (2008), `DISTRICT` (2009–2020), `STATE` (2021–2026) |
| `cpi_u_annual` | decimal | BLS CPI-U annual average, US city average, all items |
| `deflator_to_base` | decimal | see §7 |

Carrying 2015 and 2018 as explicit suspension rows is what makes the break
visible in a line chart. Without them, a visual connects 2014 straight to 2016
and implies continuity where the survey did not run.

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

All relationships single-direction, one-to-many, filtering from dimension to
fact. `dim_state → dim_geography` is a deliberate snowflake: it is what lets
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
  average, all items, annual average. Series `CUUR0000SA0`.
- **Base year:** 2025, the most recent complete calendar year. 2026 is
  incomplete as of this writing; revisit at year end (§10.2).
- **Deflator:** `deflator_to_base = cpi_u_annual[2025] / cpi_u_annual[year]`,
  stored as a column on `dim_year`.
- **Measure:** `Avg Real Rent per Acre` = nominal rent × `deflator_to_base`.

Every chart and article figure states whether it is nominal or real. Trend
claims spanning 2008–2026 use real dollars; single-year comparisons use
nominal.

---

## 8. Naming conventions

| Element | Convention | Example |
|---|---|---|
| Tables | `fact_` / `dim_` prefix, snake_case | `fact_cash_rent` |
| Surrogate and foreign keys | `_key` suffix | `geo_key`, `year_key` |
| Source natural keys | `_ansi` or `_code` suffix | `county_ansi`, `ag_district_code` |
| Measure columns | unit in the name | `rent_usd_per_acre`, `cv_pct` |
| Coded values | UPPER_SNAKE | `CROPLAND_IRRIGATED`, `SUSPENDED` |
| DAX measures | PascalCase with spaces | `Avg Rent per Acre` |

DAX measures use spaces so a measure is never mistakable for a column inside a
formula.

Planned measures: `Avg Rent per Acre`, `Avg Real Rent per Acre`,
`Rent YoY Pct`, `Counties Reporting`, `Low Confidence Share`,
`Median CV`, `Rent vs State Median`.

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

**10.2 — CPI-U base year.** Set to 2025 pending a complete 2026 annual average.
Revisit January 2027 or when BLS publishes the 2026 annual figure.

**10.3 — Suppression semantics.** The extract cannot distinguish disclosure
suppression from non-estimation (§3.6). If the article makes a claim about how
much data is withheld, that claim needs a caveat or a second source.

---

## 11. Changelog

| Version | Date | Change |
|---|---|---|
| 0.1.0 | 2026-08-29 | Initial specification. Profile complete, decisions D1–D10 resolved, open item 10.1 resolved. No transformations executed. |
