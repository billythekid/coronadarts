# Corona Darts - Craft CMS Application

Corona Darts is a PHP-based web application built with Craft CMS 4.x for managing dart leagues and competitions. The frontend uses Vue.js 3 with Laravel Mix for asset compilation and Tailwind CSS for styling.

**Always reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.**

## Working Effectively

### Prerequisites and Environment Setup
- Ensure PHP 8.3+, Node.js 20+, npm 10+, and Composer 2.8+ are available
- Copy `.env.example` to `.env` and configure database settings
- The application requires a MySQL database to run fully

### Frontend Development (Always Works)
- **Install frontend dependencies**: `npm install` -- takes 30 seconds. NEVER CANCEL.
- **Development build**: `npm run dev` -- takes 4 seconds, compiles Vue.js and Tailwind CSS
- **Production build**: `npm run production` -- takes 8 seconds, includes minification and optimization
- **Watch mode**: `npm run watch` -- for continuous development
- **Hot reload**: `npm run hot` -- for live reloading during development

### Backend Dependencies (Network Restricted)
- **CRITICAL LIMITATION**: `composer install` fails in most environments due to GitHub API rate limiting and network restrictions
- **Workaround**: If you encounter "GitHub OAuth token" prompts, press Enter to skip - the command will fail but this is expected
- **Alternative**: Use `COMPOSER_DISABLE_NETWORK=1 composer install` but this will also fail without cached dependencies
- **For local development**: Developers need their own environment with proper GitHub access tokens

### Database Setup (Required for Full Functionality)
- Configure MySQL connection in `.env` file:
  ```
  DB_DSN="mysql:host=127.0.0.1;port=3306;dbname=coronadarts"
  DB_USER="root"
  DB_PASSWORD=""
  ```
- **Cannot run without database**: The Craft CMS application requires a database connection to function
- **Admin setup**: Access `/admin` URL after database setup to configure Craft CMS

## Validation Scenarios

### Frontend Development Validation
After making changes to frontend code, ALWAYS validate with these steps:
1. Run `npm run dev` to ensure assets compile without errors
2. Check generated files in `web/assets/css/` and `web/assets/js/`
3. For production deployment: Run `npm run production` and verify minified output
4. **ALWAYS update browser data**: Run `npx update-browserslist-db@latest` -- takes 6 seconds
5. **ALWAYS fix security issues**: Run `npm audit fix` -- takes 5 seconds, may leave some unresolvable vulnerabilities
6. **Expected output files**:
   - `/assets/js/app.js` (~236 KiB in dev, ~88 KiB in production)
   - `/assets/js/vendor.js` (~1.5 MiB in dev, ~370 KiB in production)
   - `/assets/js/manifest.js` (~7 KiB in dev, ~1.5 KiB in production)
   - `assets/css/app.css` (~40 KiB in dev, ~1.3 KiB in production)

### Backend Code Validation
- **Template changes**: Modify files in `templates/` directory (Twig templates)
- **Module changes**: Modify custom Dart logic in `modules/darts/`
- **Configuration**: Modify settings in `config/` directory
- **CANNOT test backend changes**: Without database connection, backend functionality cannot be validated in this environment

### Manual Testing Scenarios (Database Required)
Since the application requires a database to run, manual testing scenarios can only be performed in a complete environment:
1. **Game Creation**: Create a new dart game through the admin interface at `/admin`
2. **Player Management**: Add players and manage their profiles
3. **Scoring Interface**: Test the Vue.js scoring components by starting a game
4. **Tournament Management**: Create and manage competitions and tournaments
5. **Score Tracking**: Verify real-time score updates and game progression
6. **Leaderboards**: Check that leaderboard calculations work correctly

## Timing Expectations and Timeouts

- **npm install**: 30 seconds - NEVER CANCEL, set timeout to 120+ seconds
- **npm run dev**: 2 seconds - NEVER CANCEL, set timeout to 60+ seconds  
- **npm run production**: 8 seconds - NEVER CANCEL, set timeout to 60+ seconds
- **npm run watch**: Continuous process - NEVER CANCEL, runs indefinitely for development
- **npx update-browserslist-db@latest**: 6 seconds - NEVER CANCEL, set timeout to 60+ seconds
- **npm audit fix**: 5 seconds - NEVER CANCEL, set timeout to 60+ seconds
- **composer install**: WILL FAIL due to network restrictions - do not attempt unless specifically needed

## Key Projects and Code Structure

### Frontend Assets (`/assets`)
- **CSS**: `assets/css/app.css` - Main Tailwind CSS entry point
- **JavaScript**: `assets/js/app.js` - Main Vue.js 3 application entry point with component registration
- **Vue Components**: `assets/js/components/` - Vue.js SFC components for dart functionality:
  - `n01-scorer.vue` - Main dart scoring component for N01 games (501, 301, etc.)
  - `cumulative-scores.vue` - Live score tracking and display
  - `lose-lives.vue` - Lives/elimination game mode component
  - `player-dropdown.vue` - Player selection interface
  - `competitions-dropdown.vue` - Competition management interface

### Backend Code
- **Templates**: `templates/` - Twig templates for all pages
  - `templates/games/` - Game-related templates
  - `templates/players/` - Player management templates  
  - `templates/competitions/` - Competition management templates
- **Modules**: `modules/darts/` - Custom Craft CMS module for dart logic
  - `modules/darts/controllers/` - Application controllers
  - `modules/darts/services/` - Business logic services
  - `modules/darts/twigextensions/` - Custom Twig functions

### Configuration
- **Craft Config**: `config/` - Craft CMS configuration files
- **Build Config**: `webpack.mix.js` - Laravel Mix configuration for asset compilation
- **Styling Config**: `tailwind.config.js` - Tailwind CSS configuration
- **Environment**: `.env` - Environment variables (copy from `.env.example`)

## Important Development Notes

### Asset Compilation
- **ALWAYS run asset compilation** after making frontend changes
- Use `npm run dev` for development builds with source maps
- Use `npm run production` for optimized production builds  
- Use `npm run watch` for automatic recompilation during development
- **ALWAYS update browser data first**: `npx update-browserslist-db@latest` to prevent warnings
- **ALWAYS run security fixes**: `npm audit fix` after dependency changes
- Generated assets are saved to `web/assets/` and should not be manually edited

### Vue.js Development Notes
- **Main application**: Vue 3 Composition API used throughout
- **Component mounting**: Components are conditionally mounted based on DOM elements present
- **Dart scoring logic**: Core functionality is in `n01-scorer.vue` for standard dart games
- **Real-time features**: Live score updates and player management
- **Game modes**: Supports various dart game formats (501, 301, elimination, etc.)

### Database-Dependent Features
- **User authentication and sessions**: Requires database
- **Content management**: All CMS functionality requires database
- **Game data and scoring**: Core application features require database
- **Admin panel**: Located at `/admin` route, requires database setup

### Network and Security Limitations
- **GitHub API rate limits**: Composer install commonly fails
- **Firewall restrictions**: Some package downloads may timeout
- **OAuth requirements**: Private repository access requires authentication tokens

## Common Tasks Reference

### Repository Structure
```
/home/runner/work/coronadarts/coronadarts/
├── .env.example          # Environment configuration template
├── README.md             # Project documentation
├── package.json          # Node.js dependencies and scripts
├── composer.json         # PHP dependencies
├── webpack.mix.js        # Asset compilation configuration  
├── tailwind.config.js    # Tailwind CSS configuration
├── craft                 # Craft CMS CLI tool (requires vendor/)
├── bootstrap.php         # Application bootstrap
├── assets/               # Source assets
│   ├── css/app.css      # Main CSS file
│   └── js/app.js        # Main JavaScript file
├── config/              # Craft CMS configuration
├── modules/darts/       # Custom application module
├── templates/           # Twig templates
└── web/                 # Public web root
    ├── index.php        # Application entry point
    └── assets/          # Compiled assets (generated)
```

### package.json Scripts
```json
{
  "dev": "mix",                    // Development build
  "development": "mix",            // Alias for dev
  "watch": "mix watch",            // Watch for changes
  "watch-poll": "mix watch -- --watch-options-poll=1000",
  "hot": "mix watch --hot",        // Hot module replacement
  "production": "mix --production" // Production build
}
```

Always focus on frontend development tasks since backend functionality requires database setup that is not available in most development environments.