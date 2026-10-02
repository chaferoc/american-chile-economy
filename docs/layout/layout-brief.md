# Report layout brief — spread redesign

**Repo path:** `docs/layout/layout-brief.md`
**Written:** 2026-10-02
**Implementer:** Claude Code, working in the local clone. Aaron reviews in Power BI
Desktop and publishes. Layout and visual styling in this report are AI-generated
from this brief; the data model, ETL, DAX and analysis are Aaron's (see README).

---

## 1. Scope and hard rules

Edit only these files:

- `american-chile-economy.Report/definition/pages/ff922cffff827540744d/` (Chile Coverage)
- `american-chile-economy.Report/definition/pages/42393513435300bbc001/` (Iron County)
- `american-chile-economy.Report/definition/pages/90a2715cea1e4c9d4864/` (Your County)
- New file `docs/layout/hh-editorial-theme.json` (the report theme, §4)
- `README.md` (append the attribution section, §6)

Do not touch:

- `american-chile-economy.SemanticModel/` — anything in it.
- The three hidden pages (`73cb9fea3dc71cfdfa62`, `6462b0d0003e86c20e53`,
  `3b003d203d0d81262d08`), `pages.json`, `report.json`, `version.json`.
- Any visual's `query`, `queryState`, projections, field bindings or
  `displayName` overrides.
- Any `filterConfig`, at page or visual level. In particular: the hidden
  Missouri / Iron / Pastureland page filters on Iron County, and the
  ALASKA-exclusion filter on the Your County State slicer
  (`b612d88e88df9165927b`).
- Visual IDs. Existing visuals keep their folder names.
- Conditional formatting on the Confidence Band and CV columns, and on the
  coverage matrix. Keep those rules exactly.
- `american-chile-economy.pbix`. It is the pre-PBIP snapshot and stays as is.

Power BI Desktop must be closed while you edit; it overwrites the folder on save.
Keep every file valid against the `$schema` it declares. New visuals get a new
20-character lowercase hex ID as their folder name and `name`.

## 2. Canvas

All three pages: `width: 1280`, `height: 828`, `displayOption: "FitToPage"`.

1280 × 828 is the proportion of two US Letter portraits side by side (17 × 11 in),
so each page is a magazine spread whose halves print as portrait pages. In the
1,140 px WordPress column it renders at about 89%.

Grid (all values in canvas units):

| Element | x | width |
|---|---|---|
| Left half | 40 | 580 |
| Gutter | 620 | 40 |
| Right half | 660 | 580 |
| Full width (header only) | 40 | 1200 |

Vertical: header band y 28–88; content y 100–788; bottom margin 40.
Spacing between stacked visuals in a column: 16.

Nothing except the header crosses the gutter, so each half survives as a
printed portrait page.

## 3. Pages

Each page gets two new textbox visuals across the full width: a **headline** and
a **dek**. The headline and dek text below is draft copy for the article editor
to review; implement it exactly as written.

- Headline textbox: x 40, y 28, w 1200, h 34. Georgia, 22 pt, bold, `#1A1A1A`.
- Dek textbox: x 40, y 62, w 1200, h 26. Segoe UI, 11 pt, `#555555`.

Existing visuals are repositioned and resized as specified. Where a chart title
changes, change only the title or subtitle literal.

### 3.1 Chile Coverage (`ff922cffff827540744d`)

Headline: **Where USDA still counts chile**
Dek: **USDA's annual survey covered four chile states through 2018 and two from
2019 to 2023. The Census of Agriculture counts every farm, every five years.**

| Visual | ID | x | y | w | h | Notes |
|---|---|---|---|---|---|---|
| Survey line chart | `f285c2f1a957cbace25d` | 40 | 100 | 580 | 330 | Keep title and subtitle text. |
| Coverage matrix | `0b759c1d5f967146d174` | 40 | 446 | 580 | 260 | Keep title and subtitle. Narrow the year columns so all 18 years and the State column fit without a horizontal scrollbar; values font 9 pt. |
| Census bar chart | `badf7ce820ebebae2e7b` | 660 | 100 | 580 | 330 | Keep title and subtitle text. |
| Sources textbox | `07ec3c9c7b3faf4ea5fe` | 660 | 446 | 580 | 120 | Keep text; Segoe UI 9 pt, `#555555`. |

Series colors (set per series on the visual, not by theme order):

- Survey line chart, by state: NEW MEXICO `#A6261C` with line width 3;
  CALIFORNIA `#3D6B8C`; TEXAS `#C08A2D`; ARIZONA `#6E8B3D`; line width 2 for
  the others.
- Census bar chart, by year: 2012 `#C9D7B8`, 2017 `#8FAF6E`, 2022 `#4F7F3A`.

### 3.2 Iron County (`42393513435300bbc001`)

Headline: **Iron County, Missouri: pastureland rent per acre**
Dek: **The cheap end of the rent scale, and the least certain. Iron's 2025
estimate of $43.50 carried a 28.7% CV, the highest of Missouri's 106 county
pasture figures. Doña Ana's irrigated estimates have not exceeded 13.2% since
CVs began in 2021.**

The headline and dek replace the line chart's current title and subtitle, which
move to the page header. Give the line chart the title **Rent per acre, by year**
and turn its subtitle off.

| Visual | ID | x | y | w | h | Notes |
|---|---|---|---|---|---|---|
| Line chart | `e5c4bee7084708e06b30` | 40 | 100 | 580 | 420 | Title as above; line `#A6261C`, width 3. |
| Notes textbox | `5f09e97076b777e0d14d` | 40 | 536 | 580 | 60 | Keep text; Segoe UI 10 pt. |
| Table | `8e950123a084d950558b` | 660 | 100 | 580 | 560 | Values 10 pt, headers 10 pt bold with wrap on; size columns so all eight fit without a horizontal scrollbar. |
| CV footnote | `3574552def3d62a9076e` | 660 | 676 | 580 | 112 | Keep text; Segoe UI 9 pt, `#555555`. |

### 3.3 Your County (`90a2715cea1e4c9d4864`)

Headline: **Look up your county**
Dek: **USDA's county cash-rent estimate for any state, county and land category,
with its precision attached.**

| Visual | ID | x | y | w | h | Notes |
|---|---|---|---|---|---|---|
| State slicer | `b612d88e88df9165927b` | 40 | 100 | 180 | 56 | Dropdown; keep its filter. |
| County slicer | `98b6e1288a010c771f24` | 240 | 100 | 180 | 56 | Dropdown. |
| Land category slicer | `c5eb71f5052f32a5ea25` | 440 | 100 | 180 | 56 | Dropdown; keep the "Land category" header. |
| Line chart | `bb1dd172924f683f8f20` | 40 | 172 | 580 | 380 | Title on: **Rent per acre, by year**; line `#A6261C`, width 3. |
| Notes textbox | `6a35157ee4134185674f` | 40 | 568 | 580 | 60 | Keep text; Segoe UI 10 pt. |
| Table | `c1c8ca869e163e5ed205` | 660 | 100 | 580 | 560 | As on Iron County. |
| CV footnote | `5db2680c438c2538c541` | 660 | 676 | 580 | 112 | Keep text; Segoe UI 9 pt, `#555555`. |

All three slicer headers: Segoe UI 10 pt, `#555555`, same as each other.

## 4. Theme — `docs/layout/hh-editorial-theme.json`

A Power BI report theme JSON that Aaron imports once (View → Themes → Browse for
themes). It must:

- `name`: `"H&H Editorial"`.
- `dataColors`: `#A6261C`, `#3D6B8C`, `#C08A2D`, `#6E8B3D`, `#4F7F3A`, `#7A7A7A`,
  `#C9D7B8`, `#8FAF6E`.
- `background` `#FFFFFF`, `foreground` `#1A1A1A`, `tableAccent` `#A6261C`.
- `textClasses`: title — Georgia 12 pt, `#1A1A1A`; label and callout — Segoe UI;
  header — Segoe UI 10 pt bold.
- Page `background` and `outspace` (the area outside the canvas) both white,
  transparency 0, so the web embed has no grey bands.
- Visuals: no border, no shadow, white background, 8 px padding; titles left
  aligned, Georgia 12 pt; subtitles Segoe UI 9 pt `#555555`.
- Axes and gridlines: gridlines `#E6E6E6`, 1 px; axis labels Segoe UI 9 pt
  `#555555`; no axis titles.
- Tables and matrices: header background `#1A1A1A` with white text, alternate
  row `#F5F5F5`, grid lines `#E6E6E6`.
- Fonts limited to Georgia and Segoe UI, both of which render in Publish to web.

Per-visual formatting in §3 that duplicates the theme can be left out of the
visual JSON so the theme governs it.

## 5. Checks before handing back

1. Every edited and new JSON file parses and matches its `$schema`.
2. `git diff --stat` touches only the files in §1.
3. For each of the three pages, list every visual's ID, type, x, y, w, h, and
   confirm no visual extends past x 1240 or y 788, and none crosses x 620–660
   except the two header textboxes.
4. Confirm by diff that no `query`, `filterConfig` or conditional-formatting
   block changed.

Report the results of these four checks. Do not commit; Aaron commits after he
reviews in Desktop.

## 6. README attribution

Append this section to `README.md`:

```markdown
## Report layout

The page layout and visual styling of the published report (canvas size,
visual positions, theme, headline and dek placement) were generated by
Claude Code from `docs/layout/layout-brief.md`. The data model, Power Query
ETL, DAX measures, validation and analysis are Aaron Harris's own work. The
pre-layout version of the report is preserved as `american-chile-economy.pbix`.
```
