# Shot Share Style Implementation

## 1. Overview

Shot Share now has a shared, palette-based frontend design system for the pages that load `assets/css/style.css`. The implementation replaces the previous gray/blue styling with the existing Golden Summer Fields palette, adds an intentional dark theme, standardizes common components, improves keyboard accessibility, and makes both the authenticated shell and standalone authentication pages responsive from large desktop screens down to mobile.

The work stays presentation-focused. `index.php` renders the supplied sidebar design, while `auth/login.php` and `auth/register.php` use a streamlined version of the supplied authentication reference. Authentication, registration, routing, forms, database behavior, and backend actions remain unchanged.

## 2. Existing Style Files Used

- `style/palette.scss` is the normalized light-theme source. It keeps one SCSS declaration for each original palette color and maps those colors to semantic UI properties.
- `style/darkmode.scss` is the normalized dark-theme source. It keeps the supplied dark palette and gives it an intentional surface, text, border, accent, feedback, and shadow hierarchy.
- `assets/css/style.css` is the browser-ready stylesheet already loaded by the authenticated application shell. Because the repository has no Sass compiler or build pipeline, the semantic theme values from both SCSS sources are mirrored here.
- `style/shot-share.html` was used as an unchanged reference for warm typography, rounded cards, soft shadows, form proportions, and restrained transitions.
- `style/shot_share_sidebar_icon_collapse (1).html` was used as an unchanged reference for the official palette, navigation treatment, radii, and sidebar spacing.

## 3. Color System

The original five-color palette remains the light-mode foundation:

- Tea Green: `#ccd5ae`
- Beige: `#e9edc9`
- Cornsilk: `#fefae0`
- Papaya Whip: `#faedcd`
- Light Bronze: `#d4a373`

Components use semantic variables instead of palette-specific colors. The most important mappings are:

| Variable | Light | Dark | Usage |
|---|---|---|---|
| `--bg-page` | `#fefae0` | `#1c1f16` | Main page background |
| `--bg-surface` | `#fffdf4` | `#2a2d1f` | Cards, panels, dialogs |
| `--bg-surface-raised` | `#faedcd` | `#3a4030` | Hovered and raised surfaces |
| `--bg-surface-muted` | `#e9edc9` | `#25291c` | Secondary panels and selected navigation |
| `--bg-nav` | `#fefae0` | `#22251a` | Sidebar and mobile navigation |
| `--bg-image` | `#e9edc9` | `#25291c` | Image wells and placeholders |
| `--text-primary` | `#33291f` | `#f3f0e4` | Headings and primary copy |
| `--text-secondary` | `#625744` | `#d8d3bb` | Supporting copy |
| `--text-muted` | `#74664e` | `#b8b097` | Dates, metadata, placeholders |
| `--accent` | `#d4a373` | `#c98f5c` | Primary actions and highlights |
| `--accent-hover` | `#c58b52` | `#d7a678` | Primary-action hover state |
| `--accent-active` | `#b9783d` | `#e5b985` | Pressed state and strong highlight |
| `--accent-soft` | `#faedcd` | `#4a3b24` | Warm low-emphasis surface |
| `--on-accent` | `#2b1b0f` | `#20150c` | Accessible text on accent buttons |
| `--link` | `#7b4821` | `#e2aa76` | Links |
| `--border` | `#b8c08f` | `#525944` | Standard component border |
| `--border-soft` | `#dddcc1` | `#3a4030` | Card dividers and subtle borders |
| `--border-strong` | `#8c9863` | `#768064` | Form-control boundaries |
| `--input-bg` | `#fffef8` | `#22251a` | Input and textarea background |
| `--focus` | `#7b4821` | `#e2aa76` | Keyboard focus outline |
| `--danger` | `#8d2f2b` | `#f2a09a` | Errors and destructive actions |
| `--danger-bg` | `#f9ded5` | `#4a2925` | Error message surface |
| `--success` | `#366238` | `#c1d6a1` | Success text |
| `--success-bg` | `#e1edd2` | `#293821` | Success message surface |

Primary text, supporting text, links, button labels, and feedback messages were checked against their intended backgrounds. For example, the main light button pairing is approximately `7.33:1`, and the main dark button pairing is approximately `6.45:1`. Dark mode primary text on the page background is approximately `14.63:1`.

The stylesheet also defines reusable typography, spacing, radius, component-height, shadow, content-width, and transition tokens.

## 4. Light Mode

Light mode is the default `:root` theme and is also available through `[data-theme="light"]`. Cornsilk is the page foundation, lightly derived warm white is used for readable cards and forms, Beige and Papaya Whip provide secondary hierarchy, Tea Green supports selected states, and Light Bronze is the main action color.

Dark brown text is used on Light Bronze instead of white because white does not provide enough contrast on the supplied bronze.

## 5. Dark Mode

Dark mode is available through `[data-theme="dark"]` and through the system-preference fallback. It uses:

- `#1c1f16` for the page
- `#2a2d1f` for cards
- `#3a4030` for raised surfaces
- `#4a3b24` for warm accent surfaces
- `#c98f5c` for actions
- warm light text rather than pure white

This is an intentional hierarchy based on `style/darkmode.scss`, not a color inversion. Form controls, borders, alerts, media wells, shadows, focus rings, selection colors, and hover states all receive dark-specific values.

## 6. Theme Switching

The stylesheet supports both explicit theme selectors:

```css
[data-theme="light"]
[data-theme="dark"]
```

It also implements automatic system detection:

```css
@media (prefers-color-scheme: dark) {
    :root:not([data-theme="light"]) { /* dark tokens */ }
}
```

Current behavior is:

1. Explicit `data-theme="light"` forces Light Mode.
2. Explicit `data-theme="dark"` forces Dark Mode.
3. Without an explicit theme, the browser uses `prefers-color-scheme`.
4. The authenticated shell, Login, and Register pages load `assets/js/main.js`, which reads and writes the `shot-share-theme` localStorage key.
5. The sidebar and authentication-page theme buttons switch modes, update their accessible labels, and save the selection.

## 7. Components Styled

- Floating desktop sidebar based on the supplied prototype
- Dynamic signed-in user initials and name
- Accessible expanded and collapsed desktop sidebar states
- Persistent sidebar preference using `shot-share-sidebar`
- Responsive six-item bottom navigation
- Server-rendered active navigation states and `aria-current`
- Sidebar create-post callout, theme action, and logout action
- Primary, secondary, danger, icon, disabled, hover, active, and focus button states
- Text, email, password, search, URL, file, textarea, and select controls
- Labels, placeholders, invalid fields, file-selector buttons, and keyboard focus rings
- Cards, raised surfaces, empty states, error messages, and success messages
- Home feed post author, date, caption, media, footer, and action areas
- Search form, responsive result grid, result cards, media placeholders, metadata, and actions
- Create-post form, upload control, and responsive image preview
- Post details, attached media, metadata panels, and destructive delete action
- Login and Register page shells, branding, introductory copy, feature summaries, form cards, validation feedback, and account-switch links
- Prepared profile-edit selectors for its existing markup
- Prepared profile avatar, bio, statistics, and post-grid component selectors
- 404 card, actions, typography, and light/dark override of its existing inline theme
- Generic dialog, backdrop, and dropdown surface styles
- Reduced-motion, higher-contrast, and print adaptations

The native JavaScript `confirm()` dialog remains browser-controlled and cannot itself be styled with CSS. The destructive button that opens it is styled.

## 8. Pages Covered

The shared stylesheet actively covers these routed or directly styled page types:

- Authenticated application shell
- Home feed
- Create Post
- Search, including empty and result states
- Post View
- Not Found / 404
- Upload and image-preview states
- Delete action UI
- Login
- Register

The stylesheet also contains prepared selectors for Profile Edit and future Profile components, but those selectors do not reach all current pages because of existing markup/import limitations described in Section 13.

## 9. Responsive Design

- Above `1024px`: full fixed sidebar and spacious content layout suitable for laptop and `1440px+` desktop screens.
- At `1024px` and below: narrower sidebar, reduced content padding, and tighter search grids.
- At `896px` and below: authentication pages move from two columns to one, center the introduction, and present the three benefits in a compact row.
- At `768px` and below: the fixed sidebar becomes a floating bottom navigation; content loses its left margin and receives safe bottom spacing.
- At `640px` and below: secondary authentication benefit copy is hidden so the form remains the focus on small screens.
- At `576px` and below: navigation becomes a three-column, two-row grid so all six links remain visible without horizontal page overflow.
- At `480px` and below: search and action layouts stack, primary actions become full width, post headers wrap, authentication controls condense, and card padding is reduced.

Images, videos, canvases, text areas, grid items, forms, and page containers are constrained to their available width. Post media uses responsive dimensions and safe object fitting. Mobile navigation includes safe-area inset support.

## 10. JavaScript Changes

`assets/js/main.js` is loaded with `defer` by the authenticated shell, Login, and Register pages.

It now:

- restores the saved Light/Dark Mode preference from `shot-share-theme`
- switches themes from the sidebar and saves the selected theme
- falls back to the system theme when no saved selection exists
- restores the expanded/collapsed sidebar preference from `shot-share-sidebar`
- updates collapse-button text, title, icon, and `aria-expanded`
- safely continues when localStorage is unavailable

`assets/js/image-preview.js`, `assets/js/delete-confirmation.js`, and `assets/js/search.js` were not changed, so their existing behavior remains intact.

## 11. Files Modified

Modified:

- `assets/css/style.css`
- `assets/js/main.js`
- `auth/login.php`
- `auth/register.php`
- `index.php`
- `style/palette.scss`
- `style/darkmode.scss`

Created:

- `STYLE_IMPLEMENTATION.md`

Inspected but unchanged:

- `assets/js/search.js`
- `assets/js/image-preview.js`
- `assets/js/delete-confirmation.js`
- `style/shot-share.html`
- `style/shot_share_sidebar_icon_collapse (1).html`

## 12. PHP Verification

`index.php` was modified only where required to render the new sidebar and load its UI script. The changes add display-only initials/name preparation, accessible sidebar markup, active-link presentation, a skip link, and the deferred `assets/js/main.js` import.

`auth/login.php` and `auth/register.php` were modified only in their document markup to load the shared frontend assets and render the new authentication layouts. Field names, form methods, validation rules, password handling, database queries, redirects, and session behavior were preserved. Authentication checks, the route map, page selection, database behavior, and backend actions were not changed. All PHP files pass PHP lint.

## 13. Limitations

- `profile/edit.php` does not import `assets/css/style.css`; connecting that standalone page would require another markup edit.
- `pages/profile.php` is empty, so the Profile link has an active state but the route has no content to display.
- `post/create.php` and `post/view.php` receive the shared CSS when routed through `index.php`; they do not contain standalone stylesheet links.
- `pages/search.php` and `pages/not-found.php` are full HTML documents that are also included inside `index.php`. CSS works through the outer shell, but correcting that pre-existing nested document structure is outside this sidebar-focused change.
- Existing Search field/schema, form-action, result-link, and 404-link inconsistencies are backend/route issues and were intentionally not changed.
- The delete confirmation uses the browser's native `confirm()` dialog, whose internal appearance is browser-controlled.
- The repository has no Sass compiler or build pipeline. SCSS was structurally validated and its values were manually synchronized with the loaded CSS.
- The authentication reference included remote images, social actions, decorative graphics, and links without matching project behavior. Those elements were intentionally omitted, and the live pages use CSS shapes and system-font fallbacks instead of external dependencies.

## 14. Testing Checklist

Completed static verification:

- [x] CSS rule blocks and structural delimiters are balanced
- [x] SCSS variables and custom-property structure are valid by static inspection
- [x] All CSS custom properties referenced by components are declared
- [x] JavaScript syntax checks pass
- [x] PHP lint passes for all PHP files
- [x] Login, Register, CSS, and JavaScript return HTTP 200 through the local Apache server
- [x] Login and Register retain their expected form field names
- [x] Changed frontend files pass `git diff --check`
- [x] Authentication, route, form, and backend logic remain unchanged

Manual browser checklist:

- [ ] Light Mode
- [ ] Dark Mode
- [ ] Theme persistence
- [ ] Sidebar collapse persistence
- [ ] System theme detection
- [ ] Desktop
- [ ] Tablet
- [ ] Mobile
- [ ] Home
- [ ] Search
- [ ] Profile - current route has no markup
- [ ] Posts
- [ ] Login
- [ ] Register
- [ ] Forms
- [ ] Image preview
- [ ] Delete confirmation
- [x] No PHP functionality changes
