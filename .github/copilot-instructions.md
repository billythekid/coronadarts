# Corona Darts - Dart League Management System

Corona Darts is a Craft CMS 3.x web application for managing darts leagues and competitions. It features a PHP backend with Craft CMS and a Vue.js 3 frontend built with Laravel Mix and styled with Tailwind CSS.

**ALWAYS follow these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.**

## Working Effectively

### Initial Setup and Dependencies
Bootstrap the repository by installing all dependencies:

1. **Install PHP dependencies:**
   ```bash
   composer install --no-interaction
   ```
   - Takes ~30-60 seconds
   - **NEVER CANCEL** - Wait for completion even if it appears to hang
   - Use `--no-interaction` flag to avoid prompts
   - **NOTE**: May show warnings about abandoned packages (redactor, reasons, swiftmailer) - this is expected

2. **Install Node.js dependencies:**
   ```bash
   npm install --legacy-peer-deps
   ```
   - Takes ~5-30 seconds 
   - **NEVER CANCEL** - Wait for completion
   - **MUST use --legacy-peer-deps** flag due to browser-sync version conflicts
   - Without this flag, installation will fail with ERESOLVE errors
   - May show deprecation warnings - this is expected

3. **Set up environment configuration:**
   ```bash
   cp .env.example .env
   ```
   - Edit `.env` to configure database connection if needed
   - Default assumes MySQL on localhost with database name `coronadarts`

### Build Process

**Frontend builds (Laravel Mix):**

1. **Development build:**
   ```bash
   npm run development
   ```
   - Takes ~4-5 seconds - NEVER CANCEL
   - Generates unminified assets for debugging (89 KiB app.js, 27.6 KiB css)
   - Creates files in `web/assets/`

2. **Production build:**
   ```bash
   npm run production
   ```
   - Takes ~8-9 seconds - NEVER CANCEL
   - Generates minified and optimized assets (24 KiB app.js, 1.23 KiB css)
   - Includes versioning for cache busting

3. **Watch mode for development:**
   ```bash
   npm run watch
   ```
   - Runs continuously watching for file changes
   - Includes BrowserSync for live reload
   - **Use Ctrl+C to stop** when done

4. **Available npm scripts:**
   - `npm run development` - Development build
   - `npm run watch` - Watch files and rebuild on changes  
   - `npm run watch-poll` - Watch with polling (for network drives)
   - `npm run hot` - Hot module replacement
   - `npm run production` - Production build

### Running the Application

**Local development server:**
```bash
php -S localhost:8000 -t web
```
- Starts PHP built-in server on port 8000
- **REQUIRES database setup** to function properly
- Without database, returns 503 Service Unavailable (expected)
- Access admin at `http://localhost:8000/admin`

**Craft CMS CLI commands:**
```bash
php craft help
```
- Shows all available Craft console commands
- **NOTE**: Most commands require database connection
- Will show "Craft can't connect to the database" if DB not configured

## Validation and Quality Checks

### Security
**ALWAYS run security audit before making changes:**
```bash
npm audit
```
- **KNOWN ISSUES**: 8 vulnerabilities (2 low, 6 moderate) as of latest check
- Includes @babel/helpers, brace-expansion, sweetalert2, webpack-dev-server issues
- Run `npm audit fix` to automatically fix resolvable issues

### Code Quality
**No built-in linting or testing configured** - this project does not include:
- ESLint configuration
- PHPUnit tests  
- Jest/Vitest tests
- Code formatting tools

## System Requirements

### Required Software
- **PHP 8.3+** (tested with 8.3.6)
- **Node.js 20+** (tested with 20.19.4)
- **npm 10+** (tested with 10.8.2)
- **Composer 2.8+** (tested with 2.8.11)
- **MySQL 8.0+** for full functionality

### Browser Compatibility
Update browser data regularly:
```bash
npx update-browserslist-db@latest
```
- **NOTE**: This command may fail due to the same browser-sync dependency conflicts
- **Workaround**: Ignore the "browsers data is 7 months old" warning during builds
- The application will still function correctly with older browser data

## Common Tasks and File Locations

### Key Project Structure
```
/
├── .env.example          # Environment configuration template
├── composer.json         # PHP dependencies (Craft CMS + plugins)
├── package.json          # Node.js dependencies (Vue, Tailwind, build tools)
├── webpack.mix.js        # Laravel Mix build configuration
├── tailwind.config.js    # Tailwind CSS configuration
├── craft                 # Craft CMS CLI tool
├── bootstrap.php         # PHP application bootstrap
├── assets/               # Source CSS/JS files
│   ├── css/app.css      # Main stylesheet (Tailwind)
│   └── js/app.js        # Main JavaScript entry point
├── templates/            # Twig templates
├── config/               # Craft CMS configuration
├── web/                  # Public web root
│   ├── index.php        # Application entry point  
│   └── assets/          # Built CSS/JS files
└── modules/              # Custom PHP modules
    └── darts/           # Main application module
```

### Frequently Modified Files
When working on features, commonly edited files include:
- `templates/` - Twig templates for frontend views
- `assets/js/` - Vue.js components and JavaScript
- `assets/css/app.css` - Tailwind styles and custom CSS
- `modules/darts/` - Custom PHP business logic
- `config/` - Craft CMS configuration

### Database and Content
- Craft CMS uses MySQL for content and configuration
- Project config stored in `config/project.yaml`
- **Admin panel**: Access at `/admin` after database setup
- **First-time setup**: Visit `/admin` to run Craft installer

## Development Workflow

### Making Changes
1. **ALWAYS start with dependency installation** (see Initial Setup above)
2. **Run development build** before starting work:
   ```bash
   npm run development
   ```
3. **Use watch mode** during active development:
   ```bash
   npm run watch
   ```
4. **Test changes** by running the development server
5. **Run production build** before committing:
   ```bash
   npm run production
   ```

### Before Committing
1. **Run security audit:**
   ```bash
   npm audit
   ```
2. **Build production assets:**
   ```bash
   npm run production
   ```
3. **Test application startup** (even if 503 without DB is expected)

## Troubleshooting

### Common Issues

**"ERESOLVE could not resolve" during npm install:**
- **Solution**: Use `npm install --legacy-peer-deps`
- **Cause**: browser-sync version conflicts

**"Could not fetch" GitHub token requests during composer install:**
- **Solution**: Use `composer install --no-interaction`
- **Alternative**: Skip GitHub token prompts with the above flag
- **Cause**: Composer occasionally requests GitHub tokens for rate limiting

**"Craft can't connect to the database" errors:**
- **Expected behavior** without database setup
- **Solution**: Configure MySQL and update `.env` for full functionality

**503 Service Unavailable when accessing application:**
- **Expected behavior** without database setup
- **Indicates PHP server is working correctly**

### Build Time Expectations
- **npm install**: 5-30 seconds - NEVER CANCEL
- **composer install**: 30-120 seconds - NEVER CANCEL  
- **npm run development**: 4-5 seconds - NEVER CANCEL
- **npm run production**: 8-9 seconds - NEVER CANCEL
- **npm run watch**: Continuous until stopped

**CRITICAL**: All build commands may take longer than expected. Always wait for completion rather than canceling and retrying.

### Expected Output Sizes
- **Development build**: app.js (89 KiB), vendor.js (1.79 MiB), app.css (27.6 KiB)
- **Production build**: app.js (24 KiB), vendor.js (447 KiB), app.css (1.23 KiB)