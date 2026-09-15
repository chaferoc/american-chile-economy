# American Chile Economy

A data journalism project on the decline of U.S. chile pepper production and the
cost of the ground it grows on, built as a documented, reproducible analytics
pipeline: USDA public data → Power Query ETL → star schema → named DAX measures
→ published Power BI report and article.

New Mexico grows most of the country's chile. Harvested acreage there has fallen
27% since 2008 and yield per acre has fallen 23%, compounding to a 44% drop in
production, while the price growers receive has more than doubled. Nationally,
domestic production roughly halved after 2014 and imports — now about 90% of
total supply — absorbed the difference, holding per-capita availability
essentially flat. This project assembles the public data behind that and asks
what land cost has to do with it.

**Status:** cash rents model built and validated; chile sources profiled;
report layer and article in progress.

---

## Data sources

All sources are public USDA data. Raw extracts are committed under `data/raw/`
so every figure in the model can be traced to the bytes it came from.

| Source | Use | Grain |
|---|---|---|
| NASS Quick Stats — county cash rents | Rent paid per acre, 49 states | county × year × land category |
| NASS Quick Stats — peppers | Chile acreage, yield, price, production | state × year |
| ERS *Vegetables and Pulses Yearbook*, Table 54 | National supply, imports, exports, per-capita availability, 1980– | year |
| NASS *2025 New Mexico Chile Production* | County splits, fresh vs. processing | validation reference |
| NASS *Cash Rents Methodology and Quality Measures* | Survey design, CVs, regions | methodology reference |

---

## Repository layout

```
data/raw/                  Quick Stats extracts, unmodified, GUID filenames
docs/data-model/           Model specification and profiling notes
docs/interview/            Producer interview instrument
```

- **`docs/data-model/cash-rents-data-model.md`** — the authoritative record of
  grain, keys, naming, transformations, measures, and every modeling decision,
  with a changelog. Written to be quotable in the article's methodology section.
- **`docs/data-model/chile-ingest-profile.md`** — source profiling for the chile
  facts, including the per-extract filter rules and the traps in them.
- **`docs/interview/interview-questionnaire.md`** — interview instrument for a
  retired USDA NRCS conservationist and cow-calf operator who has filled out the
  Cash Rents survey for years. He is a primary source on the instrument itself,
  which is not something the published data can supply.

---

## The model

Star schema, sparse fact, conformed dimensions.

| Table | Rows | Grain |
|---|---|---|
| `fact_cash_rent` | 79,064 | county × year × land category |
| `fact_cash_rent_residual` | 6,349 | rollup entity × year × land category |
| `dim_geography` | 2,938 | published county entity |
| `dim_state` | 49 | state |
| `dim_year` | 19 | calendar year, 2008–2026 |
| `dim_land_category` | 3 | irrigated, non-irrigated, pasture |

Seven relationships, all one-to-many, all single cross-filter direction —
required rather than stylistic, since three dimensions reach both facts and
bidirectional filtering would let a slicer on one fact silently reshape the
other. Thirteen DAX measures, each validated against values computed
independently from the source extract.

Real-dollar figures deflate with CPI-U (2025 base) computed in DAX, so the
index and base year can change without reloading data.

---

## How the data is treated

The interesting problems in this build were not transformations. They were
places where the source says less than it appears to:

- **Absence has three different meanings.** A county missing in 2008 wasn't in
  the survey yet; a county missing in 2015 or 2018 is a survey that Congress
  didn't require that year; a blank value may be disclosure suppression or
  non-estimation, and this extract cannot distinguish them. All three render
  identically on a chart. They are annotated separately.
- **Composition change masquerades as rate change.** The set of counties
  publishing changes every year, so a naive year-over-year figure mixes the two.
  2009 reads as a 20.7% collapse in rents that did not occur. Published figures
  use a constant panel of counties reporting in both years.
- **Estimates carry confidence, and it is published.** NASS supplies a
  coefficient of variation from 2021 forward. One county figure in this project
  was withdrawn after its CV placed it in the low-confidence band — the
  arithmetic was correct and the number was still not safe to build a claim on.
- **Nominal dollars and chosen endpoints both flatter a trend.** A single-county
  series here reads +235% nominal, +123% in real dollars, and +10% real if the
  endpoint moves by one year. Trend claims state their endpoints and survive
  moving them.

Corrections are recorded in the spec changelog rather than quietly fixed, and
several of them are corrections to earlier versions of this project's own
conclusions.

---

## Use

USDA data is in the public domain. Analysis, documentation, and modeling
decisions in this repository are the author's own. If you reuse a figure, take
it from the USDA source rather than from here — the model is documented well
enough that you can check what was done to it first.
