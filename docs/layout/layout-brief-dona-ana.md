# Layout brief addendum: Dona Ana Chart page

**Repo path:** `docs/layout/layout-brief-dona-ana.md`
**Written:** 2026-10-05
**Implementer:** Claude Code, in the local clone. Aaron reviews in Power BI Desktop.
Read `docs/layout/layout-brief.md` first. Its §1 hard rules, §4 theme and §5 checks
apply here unless this file says otherwise.

The chart on this page is Aaron's: page filters, field bindings, the two range
measures and the error-bar bounds were built and validated by him. This addendum
covers styling only, so the chart matches the published pages when it is exported
as a static image for the article.

---

## 1. Scope

Edit only:

- `american-chile-economy.Report/definition/pages/8fc6bb88e6579a6e3f80/page.json`
  (canvas size only)
- `american-chile-economy.Report/definition/pages/8fc6bb88e6579a6e3f80/visuals/f50fc8232e350c68cd57/visual.json`

Do not touch, in addition to the main brief's list:

- This page's `filterConfig` (NEW MEXICO / DONA ANA / Irrigated cropland) and its
  `visibility` (`HiddenInViewMode` stays).
- The `error` objects' `errorRange` block: the `lowerBound` (`Rent Range Low`),
  `upperBound` (`Rent Range High`), `isRelative` and selectors stay exactly as they
  are. `enabled`, `labelShow` and `barMatchSeriesColor` also stay as set.
- `categoryAxis.axisType` stays `Categorical`, so the 2015 and 2018 gaps show.
- `lineStyles.lineChartType` stays `linear`.
- The title and subtitle text literals. The `displayName` overrides that label the
  series "Nominal" and "In 2025 dollars".
- `pages.json`. (Its `activePageName` currently points at this page; Aaron resets
  the active page in Desktop before publishing. Leave it.)

## 2. Canvas

`width: 1280`, `height: 720`, `displayOption: "FitToPage"`.

The image goes into the article's 840 px text column, so this page is not a spread.
16:9 keeps the exported image at a readable height in that column.

## 3. The chart (`f50fc8232e350c68cd57`)

Position: x 40, y 40, w 1200, h 640.

Match the Iron County line chart (`e5c4bee7084708e06b30`) and the theme:

| Element | Setting |
|---|---|
| Nominal series (`_Measures.Avg County Rent per Acre`) | `#A6261C`, stroke width 3, per-series `dataPoint` selector as on Iron County |
| In 2025 dollars series (`_Measures.Avg Real Rent per Acre`) | `#3D6B8C`, stroke width 2 |
| Error bars | Keep `barMatchSeriesColor` true so the whiskers are `#A6261C` |
| Axis label font | 8 pt, as on Iron County (category and value axes) |
| Title / subtitle | Theme fonts (Georgia 12 pt title, Segoe UI 9 pt `#555555` subtitle); keep text |
| Legend | Shown, top left, Segoe UI 9 pt `#555555` |
| Padding | Left and right 0, as on Iron County |
| Border, shadow, background | Theme defaults (none, none, white) |

Do not add data labels, markers, reference lines or axis titles.

## 4. Checks before handing back

1. Both edited files parse and match their `$schema`.
2. `git diff --stat` touches only the two files in §1.
3. Confirm by diff that `filterConfig`, `visibility`, the `errorRange` block,
   `axisType`, `lineChartType`, the title and subtitle literals, and every
   `query` / projection block are unchanged.
4. Report the chart's final x, y, w, h and the two series colors and widths.

Do not commit. Aaron commits after he reviews in Desktop.
