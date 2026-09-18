# American Chile Economy

A data journalism project on how USDA measures American agriculture — and what
that measurement misses — built as a documented, reproducible analytics
pipeline: USDA public data → Power Query ETL → star schema → named DAX measures
→ published Power BI report and article.

The project began as a study of the decline in U.S. chile production. About half
of that decline turned out to be real, and the other half turned out to be the
measurement stopping. In March 2019, USDA's five-year program review removed
Arizona and Texas from the chile estimating program — not because the crop
disappeared but because, under the review's stated rule, those states no longer
accounted for enough of national production and value to justify the survey
cost. Plotted naively, the national series reads as a collapse. Much of it is
the count ending.

What is real: New Mexico's harvested acreage has fallen 27% since 2008 and yield
per acre 23%, compounding to a 44% drop in production, while the nominal price
growers receive has more than doubled — though much of that is inflation, and
the real-dollar figure is materially smaller. Nationally, production roughly
halved after 2014 and imports — now about 90% of total supply — absorbed the
difference, holding per-capita availability essentially flat since 2014.

That led to the second half of the project: the survey instruments themselves,
who depends on them, and how coverage gets decided. County cash rents are the
worked example, because the chain from a form on a kitchen table to a federal
payment rate is short and documented.

**Status:** cash rents star built and validated; chile state and census facts
built and validated; 35 assertions passing; report layer and article in
progress.

---

## Data sources

All sources are public USDA data. Raw extracts are committed under `data/raw/`
so every figure in the model can be traced to the bytes it came from.

| Source | Use | Grain |
|---|---|---|
| NASS Quick Stats — county cash rents | Rent paid per acre, 49 states | county × year × land category |
| NASS Quick Stats — peppers, survey | Chile acreage, yield, price, production | state × year |
| NASS Quick Stats — peppers, Census of Agriculture | Harvested acres, complete 50-state coverage | state × census year |
| ERS *Vegetables and Pulses Yearbook*, Table 54 | National supply, imports, exports, per-capita availability, 1980– | year |
| NASS *Program Review*, Vegetable Program, 2019 and 2024 | Which states are estimated, and why | reference |
| NASS *2025 New Mexico Chile Production* | County splits, fresh vs. processing | validation reference |
| NASS *Cash Rents Methodology and Quality Measures* | Survey design, CVs, small-area models, regions | methodology reference |

---

## Repository layout

```
data/raw/                  Quick Stats extracts, unmodified, GUID filenames
docs/data-model/           Model specification
docs/interview/            Producer interview instrument
docs/article/              Article drafts
```

- **`docs/data-model/cash-rents-data-model.md`** — the authoritative record of
  grain, keys, naming, transformations, measures, and every modeling decision,
  with a changelog. Written to be quotable in the article's methodology section.
- **`docs/interview/interview-questionnaire.md`** — interview instrument for a
  retired USDA NRCS conservationist and cow-calf operator who has filled out the
  Cash Rents survey for years. He is a primary source on the instrument itself,
  which is not something the published data can supply.
- **`docs/article/article-one-skeleton.md`** — structure, sourcing and the
  claims the article deliberately does not make.

---

## The model

Star schema, sparse facts, conformed dimensions.

| Table | Rows | Grain |
|---|---|---|
| `fact_cash_rent` | 79,064 | county × year × land category |
| `fact_cash_rent_residual` | 6,349 | rollup entity × year × land category |
| `fact_chile_state` | 62 | state × year, six measure columns |
| `fact_chile_state_residual` | 4 | residual entity × year |
| `fact_chile_census` | 150 | state × census year |
| `dim_geography` | 2,938 | published county entity |
| `dim_state` | 50 | state |
| `dim_year` | 19 | calendar year, 2008–2026 |
| `dim_land_category` | 3 | irrigated, non-irrigated, pasture |
| `chk_row_counts` | 35 | assertion |

Twelve relationships, all one-to-many, all single cross-filter direction —
required rather than stylistic, since `dim_state` and `dim_year` each reach
four facts and bidirectional filtering would let a slicer on one fact silently
reshape the others.

`chk_row_counts` is an in-model assertion table: each row states an expected
value, computes the actual, and fails loudly. It covers row counts,
fact-to-residual reconciliation, orphan keys in every relationship, key
uniqueness, and the count of suppressed rows in each fact. It caught a type
mismatch that had silently deadened a relationship, and a dimension built for
one survey's state list being reused by a fact with wider coverage.

Thirteen DAX measures, each validated against values computed independently
from the source extract. All currently sit on the cash rents star; the chile
measures are not yet built.

Real-dollar figures deflate with CPI-U (2025 base) computed in DAX, so the
index and base year can change without reloading data.

---

## How the data is treated

The interesting problems in this build were not transformations. They were
places where the source says less than it appears to:

- **Absence has four different meanings.** A county missing in 2008 wasn't in
  the survey yet. A county missing in 2015 or 2018 is a survey Congress didn't
  require that year. A blank value may be disclosure suppression or
  non-estimation. And a state that vanishes from a commodity series may simply
  have been removed from the estimating program by a five-year review. All four
  render identically on a chart, and only the last one means the thing being
  measured changed. They are annotated separately.
- **Composition change masquerades as rate change.** The set of counties
  publishing changes every year, so a naive year-over-year figure mixes the two.
  2009 reads as a 20.7% collapse in rents that did not occur. Published figures
  use a constant panel of counties reporting in both years.
- **Estimates carry confidence, and it is published.** NASS supplies a
  coefficient of variation from 2021 forward, and since the 2021 estimate year
  county rates come from Bayesian small-area models whose inputs include the
  number of reports obtained. Low-confidence figures are published here with
  their CV attached rather than suppressed: a wide CV is information about the
  survey, and hiding the number would conceal the most interesting thing in it.
- **Nominal dollars and chosen endpoints both flatter a trend.** A single-county
  series here reads +235% nominal, +123% in real dollars, and +10% real if the
  endpoint moves by one year. Trend claims state their endpoints and survive
  moving them.
- **Suppression codes are not interchangeable.** `(D)` is withheld for
  disclosure; `(Z)` is a real value too small to round up to the first unit.
  Both arrive with a leading space that makes an equality test silently fail.
  They are trimmed once at the source and classified separately, because
  treating `(Z)` as missing would drop a real observation.

Corrections are recorded in the spec changelog rather than quietly fixed, and
several of them are corrections to earlier versions of this project's own
conclusions — including the premise it started from.

---

## Use

USDA data is in the public domain. Analysis, documentation, and modeling
decisions in this repository are the author's own. If you reuse a figure, take
it from the USDA source rather than from here — the model is documented well
enough that you can check what was done to it first.
