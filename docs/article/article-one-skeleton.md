# Article one — skeleton

**Working title:** The Form That Sets the Rent
**Status:** skeleton. Bracketed `[DAD: ...]` gaps are filled from the 2026-09-19 interview.
**Series:** article one of two. Article two — decline of U.S. produce agriculture and
rising reliance on imports and shelf-stable goods — is not started until this one is
published, and references it.

**What this article is and isn't.** Article one is the data-driven piece: it makes
claims that rest on the model and on cited USDA documents, and it carries the
portfolio weight. Article two is the advocacy piece. Keep the argument here inside
what the data and the sources actually support — every load-bearing claim below has a
citation or a check behind it.

---

## Lede — Iron County, 2025

Pastureland cash rent, $43.50 an acre. One number, published by USDA, for a county in
the Missouri Ozarks. It moved sharply from the prior year and carries a CV of 28.7 —
USDA's own signal that its confidence in the figure is thin.

`[DAD: his reaction to the number, cold, before any explanation. Does it match what he
sees rented locally?]`

---

## §1 — What the number is for

Not a statistic for statisticians.

- The Cash Rents Survey is sponsored by the Farm Service Agency, which uses the county
  estimates to set market-based rates in programs including CRP.
- The chain is mechanical: county-average dryland cash rent x soil-specific
  productivity factors = the Soil Rental Rate, the maximum payment rate on a CRP offer
  in that county.
- NASS has been required since the 2008 Farm Bill to publish mean rental rates for
  every county with at least 20,000 acres of cropland plus pasture.
- Where county data is insufficient, an alternative rate must be proposed with
  supporting documentation, and FSA will not accept expert opinion as the only support.

So "why bother filling it out" answers concretely: a neighbour's conservation payment
is computed from it, and a bad county number turns into paperwork somebody else has to
file.

`[DAD: did he know this? In 30 years at NRCS did the survey-to-CRP-rate connection ever
come up? Did producers know?]`

**Sources:** NASS Guide to NASS Surveys, Cash Rents by County; FSA Notices CRP-852,
CRP-913, CRP-1012.

---

## §2 — What thin reporting does, since 2021

The part most producers probably don't know: county rates are no longer a straight
tabulation of returned forms.

- Since the 2021 estimate year NASS produces county-level rates with Bayesian
  small-area models.
- Stated model inputs: current and prior year survey ratios and standard errors, the
  prior official statistic, a National Commodity Crop Productivity Index variable, and
  **the number of reports obtained**.
- Results are benchmarked against state-level official estimates.

The consequence isn't that sparse counties get wrong numbers. It's that they get
*inferred* ones — the fewer reports, the more the published figure leans on the
productivity index, last year's number and the state benchmark rather than on what
anyone in that county reported. Iron County's CV is what that looks like from outside
the agency.

Second filter, under-discussed: the survey counts land rented for cash. Land rented
for a share of the crop, per head, per pound of gain, by AUM, free of charge, or with
buildings included is excluded.

`[DAD: in a cow-calf county, how much pasture actually changes hands on a flat
cash-per-acre basis? If most of it moves by AUM or handshake, what is $43.50
measuring?]` — **highest-value gap in the piece.** If the answer is "hardly any," that
is the article's strongest finding and it comes from the producer, not the data.

**Source:** NASS Cash Rents Methodology and Quality Measures, August 2025
(`crntqm25.pdf`, in repo).

---

## §3 — How coverage gets decided, and how fast it goes

Chile peppers as the worked example, because the documentary record is unusually clean.

- March 2019: NASS's five-year program review cut Arizona and Texas from the chile
  estimating program, effective with the 2019 crop, leaving California and New Mexico.
- Stated method: for each crop, states arrayed by production and value, largest share
  retained, given limited resources.
- Not a judgment about chile. The same review dropped eight states from tomatoes,
  eight from sweet corn, and every estimating state from lima beans, and discontinued
  all in-season vegetable forecasts.
- The primary input to that ranking is the Census of Agriculture.

Chile harvested acres, Census of Agriculture:

| State | 2012 | 2017 | 2022 |
|---|---|---|---|
| New Mexico | 9,577 | 8,313 | 8,484 |
| California | 7,029 | 4,168 | 3,257 |
| Texas | 4,288 | 2,074 | 2,249 |
| Ohio | 698 | 873 | 1,058 |
| Arizona | 1,944 | 1,250 | 386 |

- April 2024: the next review restored Texas and added Ohio. Arizona — down 80% in a
  decade, eleventh nationally — stayed out.
- Coverage isn't lost once. It's re-decided every five years against the last Census,
  and the Census is the form everyone gets.

**And the decline the annual series appeared to show was substantially the measurement
stopping.** New Mexico is down 11% across the Census decade; California 54%. The
complete fifty-state count in 2022 is 23,122 acres against at least 31,265 in 2012 —
at least a 26% fall, understated because nine states were withheld in 2012.

**Unresolved:** Florida ranked fourth in 2022 (1,371 acres) and was not added in the
2024 review. The review weighs value as well as acres and the table above is acres
only, so the ranking here is a proxy for theirs, not theirs. Do not assert a reason.

**Sources:** NASS Program Review 2019 and 2024, Vegetable Program; Census of
Agriculture via Quick Stats, in `fact_chile_census`.

---

## §4 — Close

`[DAD: he still fills it out. Why? What would make him stop?]`

The cash rents survey is statutory; the vegetable estimates are discretionary. Neither
is safe from erosion, and erosion doesn't look like cancellation — it looks like a
wider CV and a number that came out of a model.

---

## Claims deliberately NOT made here

Recorded so they don't creep back in during drafting.

- **Not** "production fell to ~175M lbs by 2022." ERS Table 54 reads 250.39M for 2022
  and the series never reaches 175M. Original project brief was wrong.
- **Not** "per-capita consumption rose while production fell." Per-capita availability
  has been flat and noisy since 2014 (7.18 lbs in 2014, 7.45 in 2025). The real rise is
  long-horizon, from 3.05 lbs in 1980.
- **Not** "New Mexico's chile industry collapsed." Down 27% in survey harvested acres
  2008-2025 and 11% across the Census decade. California and Texas are where the
  collapse happened.
- **Not** "we are about to lose the program." Chile coverage *expanded* in 2024 and
  cash rents is Farm Bill-mandated. The defensible claim is that coverage is
  discretionary, re-decided on a five-year cycle, and that quality erodes quietly
  through modelling in between.
- **Not** a New Mexico share-of-U.S. measure. The denominator would be whichever states
  NASS happened to estimate that year; the share climbs to near 100% mostly because
  other states left the program.
- **No** attribution of the 2019 chile cut to low survey response. The stated cause is
  crop size in those states, ranked off the Census.

---

## Open items

- Interview, 2026-09-19. Four bracketed gaps above.
- FSA source for §1 and a NASS regional field office statistician for §2 — both route
  through public affairs, so post-draft, not pre-interview.
- No funding or sponsorship from USDA or FSA-adjacent bodies for a piece arguing USDA
  data deserves better participation.
- Primary source still needed for the New Mexico Chile Survey being folded into the End
  of Season Vegetable Survey (~2022). Only a secondary mirror of the OMB notice found
  so far. Do not use until the Federal Register entry is located.
- Publishing: H&H homepage routes on tags. Use `science` (20) or `annual-report` (22).
