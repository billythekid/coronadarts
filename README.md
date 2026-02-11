# Corona Darts

A web application for managing dart leagues, competitions, and live scoring. Built with Craft CMS 5 and Vue 3.

> **Note:** This is a personal project and work in progress. Feel free to raise PRs or issues if you find it helpful.

## Features

- **Live Scoring Interface**: Real-time dart game scoring with Vue 3 components
- **Player Management**: Track player profiles, statistics, and performance
- **League Management**: Organize and manage dart leagues and competitions
- **Multiple Game Modes**: Support for 501, 301, elimination, and other dart game formats
- **Order of Merit**: Leaderboard and ranking system
- **Game History**: Track and review past games and results

## Tech Stack

- **Backend**: Craft CMS 5.9+ (PHP 8.3+)
- **Frontend**: Vue 3 with Composition API
- **Build Tool**: Laravel Mix 6
- **Styling**: Tailwind CSS 3
- **Database**: MySQL
- **Additional**: Axios, VueDraggable, SweetAlert2

## Requirements

- PHP 8.3 or higher
- MySQL database
- Composer 2.8+
- Node.js 20+ and npm 10+

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/billythekid/coronadarts.git
cd coronadarts
```

### 2. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` and configure your database settings:
- Update `DB_DSN` with your database name (e.g., `mysql:host=127.0.0.1;port=3306;dbname=coronadarts`)
- Set `DB_USER` and `DB_PASSWORD`
- Set `DEFAULT_SITE_URL` to match your local URL

See [Craft CMS database configuration](https://craftcms.com/docs/5.x/reference/config/db.html) for more options.

### 3. Install Dependencies

**Backend:**
```bash
composer install
```

**Frontend:**
```bash
npm install
```

### 4. Build Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run production
```

For continuous development with auto-rebuild:
```bash
npm run watch
```

### 5. Complete Setup

Visit `https://yoursite.test/admin` and follow the Craft CMS installer to:
- Set up your admin user account
- Complete the installation

## Development

### Frontend Development

The Vue 3 application is located in `assets/js/`:
- `app.js` - Main application entry point
- `components/` - Vue components including:
  - `n01-scorer.vue` - Main dart scoring interface
  - `cumulative-scores.vue` - Live score tracking
  - `lose-lives.vue` - Elimination game mode
  - `player-dropdown.vue` - Player selection
  - `competitions-dropdown.vue` - Competition management

### Asset Compilation

- **Development**: `npm run dev` - Builds with source maps
- **Production**: `npm run production` - Optimized and minified
- **Watch**: `npm run watch` - Auto-recompile on changes
- **Hot Reload**: `npm run hot` - Live reloading during development

Compiled assets are output to `web/assets/`.

### Backend Structure

- **Templates**: `templates/` - Twig templates for all pages
- **Custom Module**: `modules/darts/` - Custom Craft CMS module with controllers, services, and Twig extensions
- **Configuration**: `config/` - Craft CMS configuration files

## Craft CMS Licensing

This project uses Craft CMS Solo edition (free). If you need multiple admin users or additional features, you'll need to upgrade to Craft Pro. See [Craft CMS pricing](https://craftcms.com/pricing) for details.

## Documentation

For detailed usage instructions, see [the wiki](https://github.com/billythekid/coronadarts/wiki).

## Roadmap

- [Features roadmap](https://github.com/billythekid/coronadarts/labels/roadmap)
- [Technical roadmap](https://github.com/billythekid/coronadarts/labels/technical-roadmap)

## License

ISC
