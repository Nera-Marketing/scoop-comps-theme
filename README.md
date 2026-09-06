# scoop-competitions-child

Child theme for **Get The Scoop** (`getthescoop.co.uk`) on `nera-competitions-standard`.

| Token | Value |
|---|---|
| Slug | `scoop-competitions-child` |
| Brand | Get The Scoop |
| Text domain | `scoop-comps` |
| Func prefix | `scoop_competitions_child` |
| Const prefix | `SCOOP_COMPETITIONS_CHILD` |

## Starter placeholders (for other clones)

| Token | Meaning | Example |
|---|---|---|
| `__CHILD_SLUG__` | Theme directory / handle prefix (kebab-case) | `lavish-prize` |
| `__BRAND_NAME__` | Human-readable theme name | `Lavish Prize` |
| `__TEXT_DOMAIN__` | WordPress text domain | `lavish-prize` |
| `__FUNC_PREFIX__` | PHP function prefix (`-` → `_`) | `lavish_prize` |
| `__CONST_PREFIX__` | Version constant prefix (upper-case) | `LAVISH_PRIZE` |

## Usage

```bash
# 1. Clone / degit into themes directory
degit Nera-Marketing/nera-competitions-child-theme-starter wp-content/themes/<slug>

# 2. Fill all placeholders (script self-deletes after run)
cd wp-content/themes/<slug>
bash setup.sh <slug> "<Brand Name>"

# 3. Install frontend deps and build
cd frontend && yarn install && yarn build

# 4. Activate child theme in WP Admin
```

## File tree

```
.
├── README.md
├── setup.sh                    ← placeholder filler; gone after run
├── .gitignore
├── style.css                   ← WP header, Template: nera-competitions-standard
├── functions.php               ← enqueue bridge + Vite-handle detect + category filters
├── child-brand.css             ← :root token stub (fill via nera-apply-brand-tokens skill)
├── frontend/
│   ├── package.json            ← Vite 6 + TailwindCSS v4
│   ├── vite.config.js
│   └── src/
│       ├── main.js
│       └── main.css            ← @import tailwindcss + @source lines
├── inc/acf/.gitkeep
├── assets/css/.gitkeep
├── assets/js/.gitkeep
├── template-parts/.gitkeep
├── page-templates/.gitkeep
└── docs/colors-concept.md      ← brand direction placeholder
```

## After setup

1. `nera-apply-brand-tokens` — apply palette + typography
2. `nera-category-color-filter` — wire category → hex map
3. `nera-override-ncs-hook` / `nera-add-scoped-css` — per-feature overrides

## Consumed by

`wp-competition-child` / `nera-bootstrap-child-theme` skills — this repo is the inert boilerplate they clone; the skill owns only the clone command and decision guidance.
