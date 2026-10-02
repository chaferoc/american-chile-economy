# Editorial direction — article one

**Decided:** 2026-10-01
**Status:** Active. Supersedes the prior framing. Section-one lead revised
2026-10-01 to resolve spec §10.7.
**Repo path:** `docs/editorial-direction.md`
**Related:** `docs/data-model/cash-rents-data-model.md` (v0.3.3+)

---

## The decision

Chile is the subject. Aaron's father is the expert witness, not the
protagonist. Iron County is the counter-example, not the headline. The survey
ask moves from premise to conclusion.

The prior framing — "why producers should fill out the NASS survey" — made it
a cattle article on a spicy-food publication. Two stories wearing one name. The
new framing, "what the chile valley's best ground costs, and why we know it,"
lands the same ask at the end, having earned it.

Nothing is cut. The interview stays. What changes is what the interview is
*doing*: Kim Harris enters as the person who explains how the number gets made
— retired NRCS, fills out the cash rents survey, ranches a county where the
estimate is thin — rather than as the subject of the piece. That recast is what
makes the cattle material serve the chile story instead of competing with it.

---

## The spine, in order

### 1. Lead with the surprise

If you've heard that rising land costs are squeezing New Mexico chile, USDA's
own numbers disagree.

Doña Ana County irrigated cropland rent: $195/acre in 2009, $296 in 2026.
Up 52% nominal. In 2025 dollars, $293 → $288 — **down 2%.** Flat.

Rent on the chile valley's best irrigated ground has gone nowhere in real
terms since 2009. If something is squeezing New Mexico chile, USDA's rent
figures don't show it here.

**Why this wording (spec §10.7).** Doña Ana's irrigated rent is a county
average over ground dominated by pecans, alfalfa and cotton, and the county's
chile acreage is withheld `(D)` for 2024–2025, so the figure cannot be paired
with chile. It is the setting — expensive irrigated ground in the county that
anchors New Mexico chile — not chile's cost of production. Do not write that
land cost is or isn't what's happening to chile.

**Why Doña Ana.** The comparison is the premium end of the rent scale
(irrigated cropland in the chile valley) against the cheap end (pastureland in
Iron County). Doña Ana was New Mexico's highest irrigated county rent in 13 of
the 16 published years; Luna led in 2020 and 2024, San Juan in 2023. It also
contains the Mesilla valley and Hatch, where the grower interview lands.

### 2. Earn the claim

That is sayable with confidence because the chile valley is unusually
well-measured.

| County | Irrigated rent published | Mean CV | Max CV |
|---|---|---|---|
| Doña Ana | 16 of 16 years it appears | 7.5% | 13.2% |
| Luna | 13 of 13 years it appears | 9.8% | 13.3% |

### 3. Turn

That isn't true everywhere, and it isn't USDA's doing. It's who fills out the
form.

Enter Kim Harris — retired USDA NRCS, fills out the cash rents survey annually,
cow-calf operator in Iron County, Missouri.

- Iron County pastureland, 2025: **$43.50/acre at 28.7% CV.**
- **Eight New Mexico counties have never published an irrigated rate at all:**
  Lincoln, Harding, Grant, Catron, Cibola, San Miguel, McKinley, Otero.
- **Roughly 40% of all possible county-year-category figures were never
  published** — 55,413 of 140,826 — and the extract cannot distinguish privacy
  suppression from never-estimated.

### 4. Make it the reader's problem

The "your county" lookup page. Check your own ground and the confidence
interval attached to it.

### 5. Close on the ask

The chile answer in section one is trustworthy because enough growers filled
out a survey. The pasture answer isn't, because fewer did.

That is the whole argument, and it only works in this order.

---

## Report changes

- **Chile Coverage** page moves to the front of the published report.
- **Iron County** becomes the counter-example page rather than the lead.
- **Your County** lookup stays where it is; it carries section 4.

---

## New Mexico interview

**Do it. Keep it small.**

One grower in the Mesilla or Hatch valley. Two or three quotes. One question:
*do you look at the USDA rent figure when you negotiate a lease?*

- Yes → section one gets a human face on it.
- Never heard of it → a better story, and it still fits the argument.

Supporting voice only. If it grows into a parallel narrative, the article is
back to being two pieces struggling to stay one.

---

## Do not overclaim

There is **no** systematic finding that low-value land is measured worse than
high-value land. Median CVs are nearly identical across categories:

| Land category | Median rent | Median CV | CV > 20% | CV > 30% |
|---|---|---|---|---|
| Irrigated cropland | $152.00 | 6.2% | 2.0% | 0.4% |
| Non-irrigated cropland | $58.50 | 5.4% | 2.2% | 0.4% |
| Pastureland | $19.50 | 6.9% | 3.4% | 0.7% |

The real finding is in the tail, not the median. Pastureland estimates exceed
20% CV at roughly 1.7× the rate of irrigated. Iron County's 28.7% sits in that
tail.

**The argument is aggregate soundness hiding local uncertainty** — the national
picture looks fine, and the individual county is where it breaks down. Do not
inflate it into a claim of bias against pasture. The data doesn't support that
and a reader checking the medians will catch it.

---

## Supporting material already in hand

- **Composition vs. rate** (spec §8.5): year-over-year on the unweighted county
  mean conflates rate change with which counties reported. 2009 reads −20.7%
  unmatched against +2.3% on a constant panel. Belongs in the methodology
  section.
- **Why a 2026 figure exists before 2026 ends:** cash rent is a contracted
  price collected mid-February through June and published in August, not a
  harvest outcome. Assume the reader has never read a NASS release. Carry this
  into every version of the article.
- **Suppression semantics** (spec §3.6, §10.3): any claim about how much data
  is withheld needs a caveat or a second source.
- Email sent to the NASS Heartland Regional Field Office, 2026-09-27, about the
  2025 Iron County pastureland estimate. A reply becomes a source, or a dated
  update if it arrives post-publication. Drafting does not wait.

---

## Open question

Article two was to be U.S. agricultural decline and import reliance — the
original project storyline. Check whether anything remains in it that article
one does not now absorb. Do not start article two until article one is
published.

---

## Provenance

Every figure in this document was computed against county-scoped rows
(`County ANSI` present, rollup labels excluded) rather than quoted from the
whole-extract profile. See spec §10.5 for why that distinction is recorded.
The Iron County 2025 figure is Aaron's, established before the build began.
