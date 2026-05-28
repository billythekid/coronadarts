# Corona Darts - Craft CMS Application

Corona Darts is a PHP-based web application built with Craft CMS 5 for managing dart leagues and competitions. The frontend uses Vue.js 3 with Vite for asset compilation and Tailwind CSS for styling.

**Always reference these instructions first and fall back to search or bash commands only when you encounter unexpected information that does not match the info here.**

## Working Effectively

### Prerequisites and Environment Setup
- Ensure PHP 8.3+, Node.js 20+, npm 10+, and Composer 2.8+ are available
- Copy `.env.example` to `.env` and configure database settings
- The application requires a MySQL database to run fully

### Frontend Development
- **Install frontend dependencies**: `npm install`
- **Development build (watch mode)**: `npm run dev` — runs `vite build --watch`, rebuilds on file change. No HMR; refresh the browser.
- **Production build**: `npm run build` — runs `vite build`, minified, with content-hashed filenames

Generated assets are output to `web/dist/`. The Vite manifest at `web/dist/.vite/manifest.json` is consumed by the `vite()` Twig function defined in `modules/darts/twigextensions/LeagueTwigExtension.php`.

### Backend Dependencies (Network Restricted)
- `composer install` commonly fails in this environment due to GitHub API rate limiting
- If you encounter "GitHub OAuth token" prompts, press Enter to skip
- For local development, use your own environment with proper GitHub access tokens

### Database Setup (Required for Full Functionality)
- Configure MySQL connection in `.env`:
  ```
  DB_DSN="mysql:host=127.0.0.1;port=3306;dbname=coronadarts"
  DB_USER="root"
  DB_PASSWORD=""
  ```
- Admin setup: access `/admin` after database setup

## Validation Scenarios

### Frontend Development Validation
After making frontend changes:
1. Run `npm run build` to verify assets compile without errors
2. Check generated files in `web/dist/assets/`
3. Confirm `web/dist/.vite/manifest.json` lists the entry point `assets/js/app.js`

### Backend Code Validation
- **Template changes**: Modify files in `templates/` (Twig)
- **Module changes**: Modify custom logic in `modules/darts/`
- **Configuration**: Modify settings in `config/`
- Backend functionality cannot be validated without a database connection

## Key Projects and Code Structure

### Frontend Assets (`/assets`)
- **CSS**: `assets/css/app.css` — Tailwind entry point
- **JavaScript**: `assets/js/app.js` — Vue 3 entry point with component registration
- **Vue Components**: `assets/js/components/`
  - `n01-scorer.vue` — N01 dart scoring (501, 301, etc.)
  - `cumulative-scores.vue` — Live score tracking
  - `lose-lives.vue` — Elimination game mode
  - `player-dropdown.vue` — Player selection
  - `competitions-dropdown.vue` — Competition management

### Backend Code
- **Templates**: `templates/` (Twig)
- **Modules**: `modules/darts/`
  - `controllers/` — application controllers
  - `services/` — business logic
  - `twigextensions/` — custom Twig functions (including `vite()`)

### Configuration
- **Craft Config**: `config/`
- **Build Config**: `vite.config.js` — Vite configuration; outputs to `web/dist/`
- **Styling Config**: `tailwind.config.js`
- **PostCSS Config**: `postcss.config.js` — Tailwind + Autoprefixer
- **Environment**: `.env` (copy from `.env.example`)

## Important Development Notes

### Asset Compilation
- Always run a build after frontend changes — the Twig `vite()` helper reads `web/dist/.vite/manifest.json` at request time, so missing or stale manifest = broken assets
- Vite uses content-hashed filenames (e.g. `app-B4wSjR-9.js`) for cachebusting; the manifest maps source paths to hashed output paths
- Runtime URLs to Craft-served images (paths starting with `/assets/`) are marked external in `vite.config.js` so Rollup leaves them alone

### Vue.js Development Notes
- Vue 3 Composition API throughout
- Components are conditionally mounted based on DOM elements present
- Core scoring logic lives in `n01-scorer.vue`

### Deployment
- Hosted on Laravel Forge (server `btk-server-new`, site `darts.btk.scot`)
- Auto-deploys from the `master` branch
- Deploy script runs `composer install --no-dev --optimize-autoloader`, `npm ci`, `npm run build`, then `php craft up`

## Common Tasks Reference

### Repository Structure
```
├── .env.example          # Environment configuration template
├── README.md             # Project documentation
├── package.json          # Node.js dependencies and scripts
├── composer.json         # PHP dependencies
├── vite.config.js        # Vite asset build configuration
├── tailwind.config.js    # Tailwind CSS configuration
├── postcss.config.js     # PostCSS configuration
├── craft                 # Craft CMS CLI (requires vendor/)
├── bootstrap.php         # Application bootstrap
├── assets/               # Source assets
│   ├── css/app.css       # Main CSS file
│   └── js/app.js         # Main JavaScript file
├── config/               # Craft CMS configuration
├── modules/darts/        # Custom application module
├── templates/            # Twig templates
└── web/                  # Public web root
    ├── index.php         # Application entry point
    └── dist/             # Compiled assets (generated, gitignored)
```

### package.json Scripts
```json
{
  "dev": "vite build --watch --mode development",
  "build": "vite build"
}
```
