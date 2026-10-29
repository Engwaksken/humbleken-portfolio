# Accessibility review record

This records code-level checks made during development. It is **not** a WCAG conformance claim. PRD release gate 5 still requires a manual keyboard and assistive-technology review before launch.

## Code review — 2026-10-29 (Phase 3)

Scope: public layout, homepage, enquiry form, projects and articles pages.

| Check | Result |
|---|---|
| Skip link to `#main-content`, visible on focus | Present |
| One `h1` per page; sections use `aria-labelledby` headings | Present |
| Landmarks: `header`, labelled `nav`, `main`, `footer`; search form uses `role="search"` | Present |
| Current page marked with `aria-current="page"` in navigation and category filters | Present |
| Visible focus outline on links, buttons and inputs | Present |
| Form fields have `<label>`s; required fields marked; errors linked via `aria-describedby` and `aria-invalid`; summary uses `role="alert"`; confirmation uses `role="status"`; input retained after errors | Present (enquiry form, article search) |
| Decorative arrows hidden with `aria-hidden` | Present |
| Dates use `<time datetime>` | Present (articles) |
| Spam honeypot hidden from assistive technology and removed from tab order | Present |
| Colour contrast of text pairs (WCAG 2.x formula) | **Fixed.** Brand teal `#087e8b` measured 4.47:1 on the page background and 4.23:1 on the contact background, below the 4.5:1 AA minimum for normal text. Changed to `#077784` (4.64–5.28:1 across backgrounds). Muted text `#506176` is 5.57–6.34:1; error text `#b42318` is 6.57:1. |

## Still to do (manual)

- Keyboard-only walk-through of every journey at mobile and desktop widths.
- Screen reader pass (for example NVDA with Firefox, VoiceOver with Safari), including enquiry errors and search results.
- 200% zoom and 320px reflow check.
- Admin panel (Filament) review, which this record does not cover.
- Record results, tools, versions and date here.
