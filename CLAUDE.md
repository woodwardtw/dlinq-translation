# Project
Understrap 1.x child theme for translation dislay. Bootstrap 5, WordPress 6.x.

## Commands
- `npm run watch` — watch + compile SASS and JS (no BrowserSync)
- `npm run watch-bs` — watch + compile SASS and JS + BrowserSync live reload
- `npm run build` — production build (runs `npm-run-all css js copy-assets`)
- Theme directory: `/wp-content/themes/dlinq-translation/`

## Patterns

### Javascript
- Custom Javascript additions go in `src/js/custom-javascript.js`

### Styling
- CSS modifications go in `src/sass/theme/_theme.scss`
- Global SASS variables: `src/sass/theme/_theme_variables.scss`
- Do NOT edit files under `src/sass/understrap/` (parent framework)

### Templates
- Page templates: `/page-templates/`
- Content loops: `/loop-templates/`
- Override parent templates by copying them here, not editing the parent theme

## Gotchas
- Bootstrap is loaded via npm, not CDN
- All JS/SCSS must go in `src/` and be enqueued via WordPress — never add `<script>` or `<link>` tags directly in templates
