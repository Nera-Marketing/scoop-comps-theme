# Get The Scoop child theme

The brand layer for `getthescoop.co.uk` on top of `nera-competitions-standard`. It owns
design tokens, scoped CSS, site-specific ACF fields, and the small number of template forks
where the parent offers no hook. It owns no competition logic.

## Language

### Branding

**Accent**:
The single brand colour, `#B3CDE6`, sampled from the logo mark. A **tint**, not a text or
icon colour — 1.64:1 against white, so it can only ever carry black type (12.79:1).
_Avoid_: light blue, primary blue, `#5b9bd5` (a mid-blue that was never in the logo)

**Canvas**:
The page ground behind all content, token `--color-background-light`, applied to `body`.
_Avoid_: background, page background

**Surface**:
An elevated panel sitting above the canvas — cards, the header bar, modals. Token
`--color-surface`. Distinct from Canvas so a child can lift or dim "card white"
independently.
_Avoid_: card background, white

**Token bridge**:
`child-brand.css` — a plain stylesheet enqueued after the parent's Vite bundle whose
`:root` block retunes the parent's Tailwind `@theme` custom properties. The cheapest rung
of the override ladder.
_Avoid_: theme options, customizer settings

### Footer

**Brand column**:
The first, double-width column of the site footer. Code-driven in this child: the
Customizer logo, the *Brand Text* field, and social icons. It deliberately does **not**
render the `footer-1` widget area.
_Avoid_: footer column one, about column, footer widget one

**Contact column**:
The fourth and last footer column. Also code-driven: heading, phone and email from ACF.
Renders nothing at all when both phone and email are empty, rather than leaving a bare
heading. It deliberately does **not** render the `footer-4` widget area.
_Avoid_: legal column, footer column four

**Footer widget areas**:
`footer-2` and `footer-3` — the only footer columns still populated from wp-admin widgets,
because link lists belong in widgets and menus. `footer-1` and `footer-4` are registered by
the parent but rendered nowhere; see
[ADR-0001](../../../docs/adr/0001-fork-footer-template-in-child.md).
_Avoid_: sidebars

### Page composition

**Page Components**:
The ACF flexible-content field (`page_components`) that composes a page from Timber
component layouts. When any rows exist, the page template renders them and skips its
legacy section loop.
_Avoid_: flexible content, builder, Gutenberg blocks

**Legacy template fields**:
The older ACF field groups bound to a named page template (e.g. homepage `homepage_sections`
and per-section keys under `inc/acf/homepage/`). Used only when Page Components is empty.
_Avoid_: old ACF, page template fields, hardcoded sections

**Page Hero**:
The shared inner-page banner composition — centered title, optional description and
eyebrow, on the primary gradient — matching `page-hero.php`. Distinct from
**HomepageHero**, which is the homepage-only image + dual-CTA composition. Intended as a
Page Components layout for Contact and the same family of pages (blog list, WooCommerce
listing heroes, etc.). Child layout name `NeraPageHero`.
_Avoid_: HomepageHero, contact hero tab, page-hero partial (when you mean the CMS layout),
NeraContactHero

**Page Components assignment**:
Writing `page_components` rows onto the Contact page via the Contact migrator so the
template renders `NeraPageHero` + `NeraContact`. Scaffolding layouts alone does **not**
assign them — empty rows leave the legacy Contact group visible and the page on the
`Contact` fallback. Contact migration is not done until rows exist on the page.
_Avoid_: components created, layouts registered (when you mean the page is migrated)

**Contact migration**:
The repeatable task of turning the Contact page from Legacy template fields
(`group_contact_page`) and/or the monolithic parent `Contact` layout into Page Components
rows: **Page Hero** (`NeraPageHero`) then **Contact — Details** (`NeraContact` — address +
form only). Hide parent `Contact` from Add Component
(`inc/acf/page-components/acf-hide-contact-layout.php`); hide legacy group when rows exist
via child `inc/legacy-acf-visibility.php` (parent visibility list does not include Contact
yet). Child-fork `page-templates/contact-template.php` so
`<main id="main" class="nera-contact-page bg-gray-50" role="main">` owns chrome; section
twigs only — see [ADR-0003](../../../docs/adr/0003-fork-contact-template-in-child.md).
Migrator: `inc/migration/contact-to-page-components.php` (+ `-cli.php`); HTTP requires
`NERA_ALLOW_CONTACT_MIGRATOR`. Operational defaults match Homepage migration. **Never edit
the parent theme.**
_Avoid_: Contact component with hero, HomepageHero on Contact, edit parent Contact

**Blog migration**:
The repeatable task of turning the posts index (`page_for_posts`, rendered by `home.php`)
into Page Components rows: **Page Hero** (`NeraPageHero`, create-or-reuse) then **Blog
List** (`NeraBlogList` — featured post picker + seeded empty-state copy; grid and
pagination from the query). Child-fork `home.php` is required — WordPress ignores page
templates on the posts index — and rows must be read with an explicit posts-page ID (bare
`get_field` / `nera_render_page_components()` would hit the loop’s first post). See
[ADR-0004](../../../docs/adr/0004-fork-home-php-in-child.md). Migrator:
`inc/migration/blog-to-page-components.php` (+ `-cli.php`); HTTP requires
`NERA_ALLOW_BLOG_MIGRATOR`. No legacy ACF group to hide. **Never edit the parent theme.**
_Avoid_: bare nera_render_page_components on home.php, page template on posts page,
duplicate featured in grid, blank empty-state fields

**Listing migration**:
The repeatable task of turning the competition listing page family — WooCommerce Shop /
All Competitions (`shop-template-bridge`), Closed Prizes, and Entry List — into Page
Components. Shared **Page Hero** (`NeraPageHero`, create-or-reuse, including `variant`);
query-driven lists `NeraCompetitionsList`, `NeraClosedPrizesList`, `NeraEntryList` (empty-
state fields only, seeded from template PHP / legacy empty ACF); fully editable
`NeraWhyChooseUs` replacing `trust-features` hard-coding. No template forks — all four
parent templates already call `nera_render_page_components()`. Migrator:
`inc/migration/listing-to-page-components.php` (+ `-cli.php`); HTTP requires
`NERA_ALLOW_LISTING_MIGRATOR`. Legacy listing groups hidden via child
`inc/legacy-acf-visibility.php` when rows exist. Resolve by template / WC page ID, not
slug. **Never edit the parent theme.**
_Avoid_: per-page hero twins, CategoriesCompetitions for the listing grid, blank empty
states, empty Why Choose Us repeater with runtime fallback only

**How It Works migration**:
The repeatable task of turning the How It Works (“How to Play”) page from Legacy template
fields (`group_how_it_works_page`) into Page Components rows that **reuse** the parent
layouts `HowItWorksHero`, `HowItWorksDraw`, `HowItWorksPostal`, and
`HowItWorksTransparency` (including Hero CTA link + footer text). Migrator **seeds all
defaults into rows** — titles, copy, CTA link/footer, and full repeaters (Hero steps,
Postal steps, Transparency features) — so every value is visible and editable in CMS
(saved legacy → ACF default → hardcoded `get_data()` / HIW helpers). Create child
`NeraHowItWorks*` layouts only if a real field/markup gap appears. Migrator:
`inc/migration/how-it-works-to-page-components.php` (+ `-cli.php`); HTTP requires
`NERA_ALLOW_HIW_MIGRATOR`. Operational defaults match Homepage migration. Parent already
hides `group_how_it_works_page` when `page_components` is non-empty.
_Avoid_: How to Play page rebuild, clone HowItWorks components, NeraHowItWorks rename,
runtime-only CTA defaults, empty-repeater-runtime-fallback, homepage QuickGuide

**About Us migration**:
The repeatable task of turning the About Us page from Legacy template fields
(`group_about_us_page`) into Page Components rows split by legacy section (Hero,
Narrative, Two columns, Call to action). Prefer **reusing** an existing layout when one
already matches that section (e.g. homepage `About`); otherwise create **child-owned**
layouts named `NeraAboutUsHero`, `NeraAboutUsNarrative`, `NeraAboutUsColumns`,
`NeraAboutUsCta` with **page-prefixed** labels (`About Us — Hero`, etc.). Markup is carved
from parent `AboutUsPage/template.twig` into those four child views (**section markup
only** — no outer `nera-about-us-page` wrapper; the page template `<main>` owns chrome).
`index.php` / `fields.php` / `template.twig` all live in the child (unique names). Heading
style fields + `inc/heading-style-bridge.php` apply to **Hero only**. **Never edit the
parent theme.** Do not use monolithic `AboutUsPage` for new migrations. Hide `AboutUsPage`
from the Page Components “Add Component” dropdown via a child `acf/load_field` filter (do
not delete parent files). Migrator: `inc/migration/about-us-to-page-components.php` (+
`-cli.php`); HTTP requires `NERA_ALLOW_ABOUT_US_MIGRATOR`. Operational defaults match
Homepage migration. New homepage child-only layouts use `NeraHomepage<Section>`.
_Avoid_: AboutUsPage migration, single about component, delete parent About,
page-cloned duplicate of an existing layout, parent theme edits, remove parent layout files,
duplicate page wrapper in components, bare Hero/CTA labels in Add Component

**About Us column icon**:
A curated Material Symbols ligature chosen per Two Columns card (`story_left_icon` /
`story_right_icon` selects on `NeraAboutUsColumns`). Defaults `groups` / `lightbulb`.
_Avoid_: hardcoded Twig ligature, free-text icon name, image-upload icon for this section

**About Us column body**:
Plain-text copy for each Two Columns card (`story_*_content` as `textarea`). Rendered as
safe paragraphs; empty body uses i18n placeholders. Must not store front-end card/section
HTML.
_Avoid_: WYSIWYG column body, nested card chrome in the field, CMS placeholder fields for
empty story states

**Homepage migration**:
The repeatable project task of turning a site's homepage from Legacy template fields into
filled Page Components rows (reusing parent layouts where names already exist; child-owned
layouts only when the parent has none or the field shape must differ — named
`NeraHomepage<Section>`), then hiding the legacy field group in the CMS. Flexible-content
layout labels use the human section heading, not the technical component name. The section
list is the parent homepage inventory (`homepage_sections` when set, otherwise the template
default order) — not a project brief's redesigned composition.
_Avoid_: homepage rebuild, convert homepage

**Homepage migrator**:
A guarded PHP script in the child theme that copies Legacy template field values into Page
Components rows (including media sideload). Invoked by agent or human via dual path:
WP-CLI/`php` + `wp-load.php` when available, else a capability-gated admin URL with a
one-time token. If `page_components` already has rows, the migrator **refuses** unless an
explicit force flag is passed. Empty parent stubs (e.g. blank `winners-section.php`) are
skipped, not scaffolded. Field values resolve **saved legacy → legacy ACF default_value →
hardcoded `get_data()`/template fallback**, and the resolved value is written into the
flexible row so hiding legacy does not blank the front end. Image fields always store a
Media Library attachment ID: reuse if already in media, otherwise the migrator downloads/
sideloads and assigns the new ID. Distinct from the Cursor skill that authors the script
and any child-only layouts.

Implementation: `inc/migration/homepage-to-page-components.php` (+ `-cli.php` entry).
HTTP registration requires `NERA_ALLOW_HOMEPAGE_MIGRATOR` in `wp-config.php`. Legacy Home
Page Group hide: `inc/legacy-acf-visibility.php`.
_Avoid_: WP-CLI migration, manual CMS paste, silent overwrite, invent empty sections,
leave-empty-and-hope-runtime-fallback, store-raw-URL-only

**Migrator gate**:
The `NERA_ALLOW_<PAGE>_MIGRATOR` constant that must exist in `wp-config.php` before a migrator
registers its HTTP endpoint and its Dashboard "Run migration" notice. Absent by default, so an
authored migrator is inert until someone opens the gate, runs it, and closes it again.
_Avoid_: migrator enabled, migrator installed

**Migration run**:
The act of executing a migrator so `page_components` gains rows — as distinct from a migrator
merely existing on disk. An unrun migration presents as empty Page Components *and* a
still-visible legacy ACF group; the visible group is a symptom of the empty rows, never an
independent fault.
_Avoid_: migration done, migration complete (when only files were authored)

### Prizes

**Prize**:
What a customer is competing to win. In code this is a WooCommerce product of type
`lottery`, and the parent's own vocabulary is "competition" — expect both. Client-
facing copy says Prize.
_Avoid_: giveaway, lot, item

**Spotlight prize**:
The single prize featured prominently below the homepage hero. Distinct from the parent's
`FeaturedCompetitions` component, which despite its name renders an **"Ending Soon"
carousel** of six — do not conflate the two.
_Avoid_: featured competition, hero prize

**Section footer CTA**:
A button-style call-to-action under a Page Components section (label + URL). On the
homepage, How to Play (`QuickGuide`) and Categories (`CategoriesCompetitions`) are the
examples — distinct from a section footer link.
_Avoid_: footer button, bottom CTA, hard-coded CTA, View All link (when you mean this CTA)

**Section footer link**:
A text link under a Page Components section (label + URL; FAQ may also have a prefix).
Testimonials and FAQ use this shape — not a button CTA.
_Avoid_: footer CTA, read more link (unless you mean this term), contact link chrome

**Draw date**:
When the winner is drawn, from `_lty_end_date_gmt`. On cards the parent renders it only as
a relative countdown, never as an absolute date.
_Avoid_: end date, close date, expiry
