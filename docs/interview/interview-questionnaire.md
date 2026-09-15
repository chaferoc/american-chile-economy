# Interview: NASS Cash Rents from the producer's side

**Subject:** Aaron's father — retired USDA NRCS; cow-calf operator, Iron County, Missouri
**Date:** week of 2026-09-19
**For:** "The American Chile Economy" / H&H Data Desk — methodology section and human throughline
**Format:** in person, recorded with permission

---

## What this is

*Read this aloud or hand it over. It sets up every question that follows.*

I'm building a data project about American chile peppers — where they're grown,
what's happened to domestic production, and what it costs to farm the ground
they're grown on. New Mexico grows most of the country's chile, and production
there has fallen a long way while Americans eat more of it every year. The
difference is coming in from Mexico and Peru.

The piece of it you're in is land cost. To say anything about what it costs to
rent farm ground, I'm using USDA's Cash Rents survey — the one you fill out
every year. It's the only source that publishes a rent figure for individual
counties, all 3,000-odd of them, going back to 2008.

I've loaded the whole thing: about 79,000 county-year-by-land-type figures,
built into a working model with the math checked. What I have is every number
the survey ever published. What I don't have is any idea what happens between a
man at a kitchen table with a form and a number on a government website. You're
on both ends of that — you filled the form out for years, and you used the
published results at NRCS.

Three things I'm hoping to get:

1. **What the form can't capture.** The survey only counts pasture rented for
   cash by the acre. If that's not how ground actually changes hands around
   here, then the published number for Iron County describes a slice of the
   market and not the market. You'd know.
2. **What the number gets used for,** by lenders, landowners, FSA, appraisers —
   and whether it's any good for that.
3. **One specific oddity.** Iron County's 2025 pasture figure nearly tripled and
   then fell back. I want to know whether anything actually happened here in
   2025, or whether the number is just wrong. Either answer is useful and I'd
   rather have the true one.

You get the final say on your name, and you'll see any quote before it runs.

---

## Glossary

*Terms I'll use. None of this is complicated, but the words are ugly.*

**Cash rent.** Rent paid in dollars per acre for the year, agreed up front,
regardless of what the ground produces. The survey counts only this. Share
leases, per-head, per-AUM, handshake, rent-free, and anything bundled with
buildings are all explicitly excluded.

**Estimate.** Nothing NASS publishes is a count. They survey a sample and scale
it up. Every number in here is an estimate, including the ones that look exact.

**Ratio estimate.** The county rate is total rent paid divided by total acres
rented — not the average of what each operator said. A man renting 2,000 acres
moves the county number more than a man renting 40.

**Median vs. average.** Median is the middle one: line all 106 Missouri counties
up by rate and take number 53. It's the one to use here, because a few
high-rent counties would drag an average up and make the middle of the state
look richer than it is.

**Rank.** Where the county falls among Missouri counties that published a rate
that year, highest to lowest. Ties share a rank. The number of counties changes
year to year, so the rank is always "of" something — 40 of 106.

**CV — coefficient of variation.** The honesty number. NASS publishes it
alongside the rate, as a percentage, and it says how much the rate would bounce
around if they ran the survey again. Low CV, few reports disagreed and there
were enough of them. High CV, the number is resting on thin support. Rough
reading: under 10% is solid, 10–20% is usable with care, over 20% means don't
hang a claim on it by itself. Iron County's 2025 figure carries 28.7%.

**Year-over-year (YoY).** Change from one year to the next, as a percentage.
The trap is that the set of counties reporting changes every year, so part of
any move is different counties rather than different rents. I compute it two
ways and only publish the one that compares the same counties both years.

**Nominal vs. real dollars.** Nominal is the dollar figure as published. Real is
adjusted for inflation so an old dollar and a new one mean the same thing.
$13/acre in 2009 is about $19.50 in today's money — so a rise from $13 to $22
is much less of a rise than it looks.

**Withheld.** When too few operations report, NASS suppresses the figure rather
than publish something that could identify somebody. It shows as blank, or as
(D) in their printed tables. The frustrating part is that a withheld figure and
a never-estimated one look identical in the data.

**Other counties.** The leftover bucket. Counties too thin to publish on their
own get rolled together into a state or district line so the total still adds
up.

**2015 and 2018.** The survey didn't publish county figures those years —
Congress only required it every other year until a 2018 law made it annual. A
gap in the chart there means no survey, not no rent.

---

## 0. Before you start

- [ ] Ask permission to record.
- [ ] **Attribution is his call.** Offer three options explicitly and write down
      which he picks: full name; "a retired NRCS conservationist in southeast
      Missouri"; or background-only, no quotes. Settle it before the substance,
      not after.
- [ ] Tell him you'll send him any direct quotes before publication.
- [ ] Have the Iron County one-pager (§5) printed and on the table, face down
      until §5.

---

## 1. Who he is

The article needs his standing on the page in one or two sentences. Get enough
that the reader knows why his answer carries weight.

1. Career with USDA NRCS — title at retirement, years of service, what the job
   actually involved day to day, which counties he covered.
2. Certifications, technical specialties, anything he'd want listed if his name
   runs.
3. How long he's been running a cow-calf operation. Herd size now, and whether
   that's changed much.
4. The operation came from his father-in-law — when did he take it over, and how
   did that transition work? Was there a handover period where they ran it
   together?
5. His wife grew up on that ground. What did the place look like when she was a
   kid, and what's different now?
6. What has he changed since taking it over — fencing, water, rotational
   grazing, hay ground, herd genetics, anything he'd point to as his mark on
   the place?
7. Does he rent any ground, or rent any out? On what terms?

---

## 2. Filling out the survey

He's the rare source who is inside the data-generating process. This is the
section that can't be reported any other way.

8. How many years has he been filling out the Cash Rents survey? How did he end
   up on the list?
9. Walk through it — mail, web, or phone? How long does it take? Does he answer
   from records or from memory?
10. The form asks for cash rent per acre *or* total dollars paid. Which does he
    use, and does he have to do arithmetic to answer?
11. Has anyone from NASS ever called to follow up or check an answer?
12. Does he think most of his neighbors respond? Why or why not? What would it
    take to get more of them to?
13. Does he see the results when they're published? Does he go looking?

---

## 3. What the form excludes — the methodology question

This is the highest-value line of questioning in the interview. If the answer
to 14 is "most of it," the published pastureland rate describes a fraction of
the actual market, and that's a finding with a named source behind it.

14. The questionnaire explicitly excludes pasture rented on a per-head or
    animal-unit-month basis. **Around Iron County, how does pasture actually
    change hands — per acre, per head, per AUM, or on a handshake?** Roughly
    what share is per-acre cash?
15. If a lot of it is per-head, what does that mean for a number published as
    "the county's pastureland cash rent"?
16. The form also excludes leases that include buildings, flex or cash-share
    arrangements, and whole-farm rentals. How common are those locally?
17. What does he do when a real arrangement doesn't fit any box on the form —
    skip it, round it, force it into the closest option?
18. Item 5 asks whether rented acres came from relatives. How much ground around
    there is rented family-to-family, and does family rent track the market? Is
    it typically below market?
19. Has he ever left a question blank because he genuinely didn't know? What
    happens then, in his understanding?

---

## 4. Using the data

20. In the NRCS job, what did he use NASS cash rents *for*? Who else relies on
    them — FSA, lenders, landowners, appraisers, extension?
21. Does he use the published figure in his own operation — setting rent,
    valuing his own ground, negotiating?
22. What happens when a county's number is withheld or never published? Does
    anyone notice? What do people use instead?
23. If the survey stopped tomorrow, what would break?
24. What does he think the number is *for* — and does he think it's accurate?

---

## 5. Iron County, the actual numbers

Turn the one-pager over here, not earlier. Let him react before you explain
anything.

**Pastureland cash rent, Iron County, MO — complete published series.** Dollars
per acre as published, CV where NASS publishes one, against the Missouri county
median and his rank among Missouri counties publishing that year.

| Year | Rate | CV | MO median | Rank |
|---|---|---|---|---|
| 2008 | *not published* | — | 26.50 | — |
| 2009 | 13.00 | — | 26.00 | 103 of 104 |
| 2010 | 14.50 | — | 25.50 | 104 of 109 |
| 2011 | 17.00 | — | 24.50 | 81 of 94 |
| 2012 | 17.00 | — | 27.50 | 77 of 87 |
| 2013 | 18.50 | — | 27.50 | 82 of 100 |
| 2014 | 19.00 | — | 28.50 | 82 of 101 |
| 2015 | *survey did not run* | — | — | — |
| 2016 | 25.00 | — | 32.50 | 62 of 95 |
| 2017 | 20.00 | — | 31.00 | 76 of 95 |
| 2018 | *survey did not run* | — | — | — |
| 2019 | 27.00 | — | 32.50 | 59 of 94 |
| 2020 | 20.00 | — | 35.25 | 97 of 104 |
| 2021 | 18.50 | 6.0 | 34.00 | 99 of 107 |
| 2022 | 16.00 | 12.0 | 34.75 | 102 of 106 |
| 2023 | 13.50 | 16.1 | 37.50 | 106 of 107 |
| 2024 | 16.50 | 7.0 | 35.50 | 97 of 101 |
| **2025** | **43.50** | **28.7** | 39.75 | **40 of 106** |
| 2026 | 22.00 | 8.3 | 39.75 | 95 of 104 |

*2008: Iron County wasn't in the survey yet — the county coverage was smaller
in the program's first year. 2015 and 2018: no county figures published at all.
Those are different kinds of gap and they look the same on a chart.*

25. Open with no framing: **"Does that look right to you?"** Let him talk.
26. Iron County pasture sits near the bottom of Missouri in most years — usually
    40–60% of the state median. Is that what he'd expect? What is it about the
    ground there?
27. **The 2025 number.** It nearly triples, jumps to 40th in the state, then
    drops back to $22 the next year. Did anything actually happen to pasture
    rents around there in 2025 — a big lease, an outside buyer, someone paying
    far over the going rate? Or does he think that number is wrong?
28. Tell him the 2025 estimate carries a 28.7% coefficient of variation, the
    highest in its series, and what that means in plain terms — the published
    number rests on very few reports and NASS is signaling low confidence. Does
    knowing that change how he'd read it?
29. Statewide, the estimates have gotten *less* precise since 2021, even for the
    same set of counties. Does he have a theory — fewer people responding, fewer
    operations, something else?
30. If a lender or a landowner used the 2025 figure to set rent on his ground,
    what would happen?

---

## 6. Close

31. What should someone writing about farmland rent understand that they'd
    probably get wrong?
32. Anything he expected to be asked and wasn't?
33. Anyone else worth talking to — a neighbor, an extension agent, a former
    NRCS colleague?

---

## Notes to self

- Don't lead him to the answer on Q27. The value of that question is that he
  either confirms a real market event or says the number looks wrong — and both
  outcomes are publishable.
- Q14–15 and Q27–29 are the two sections that could change the article. If time
  runs short, protect those.
- He is a primary source on the instrument, not a statistician. Ask what he
  does and sees, not what NASS methodology says.
- The glossary is for him, not for the article. Don't read it start to finish —
  reach for an entry when a term comes up.
- Every figure in the §5 table was verified against the extract on 2026-09-14.
  If he disputes one, that's a finding, not a typo to apologize for.
