# Changelog

All notable changes to Genes Framework will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added
- **Official Website**: Complete marketing website built with Genes itself (website/)
  - Professional landing page showcasing framework features
  - Interactive documentation page
  - Examples gallery with live demos
  - Download/installation guide
  - Responsive navigation and footer
  - Author credits (Devrim Vardar - @DevrimVardar)
- **Example Reorganization**: Restructured examples to match actual use cases
  - 1-landing-page: Multi-language landing page
  - 2-blog-system: Complete blog with database
  - 3-rest-api: Custom REST API implementation
  - 4-builtin-api: Zero-code REST API demo
- **Documentation Updates**: Enhanced API documentation and quickstart guides

### Changed
- Cleaned up example structure (removed unused examples)
- Updated README with accurate feature descriptions
- Improved routing system documentation

### Fixed
- View routing now requires explicit `function` property in config.json
- Template path resolution for multi-page applications
- Navigation links and asset paths for base tag compatibility

---

## [2.0.0] - 2025-10-11

### Added
- **AI-Optimized Architecture**: Complete rewrite with AI coding agents in mind
- **Single-File Design**: All framework code in 3 files (genes.php, genes.js, genes.css)
- **Universal Database Schema**: 5-table schema (persons, clones, links, nodes, events)
- **REST API Layer**: Built-in `api.handle()` for automatic CRUD endpoints
- **Enhanced Security**: Bcrypt + HMAC pepper for passwords
- **Performance Tracking**: Built-in logging and performance metrics
- **Template System**: Simple template rendering with auto-escaping
- **Module System**: Plugin architecture for extensibility
- **Function Registry**: Dot-notation namespaced functions (`g::run("db.select")`)
- **CSS Framework**: Complete responsive framework with dark/light themes
- **JavaScript Library**: Frontend utilities, API integration, event delegation

### Changed
- Complete API redesign for consistency
- Database layer now uses PDO exclusively
- Simplified configuration system
- Improved error handling and logging
- Better documentation structure

### Security
- SQL injection prevention via PDO prepared statements
- XSS protection with template auto-escaping
- CSRF token generation and validation
- Secure password hashing (bcrypt + pepper)
- Session security improvements

### Performance
- Reduced initialization time (~2-5ms)
- Optimized database queries
- Efficient caching system
- Minimal memory footprint (~2-4MB)

---

## [1.0.0] - 2024-01-01

### Added
- Initial release
- Basic PHP framework structure
- Database abstraction layer
- Simple routing
- Authentication system
- Template rendering
- Frontend JavaScript utilities
- CSS framework foundation

---

## Version Numbering

- **Major version** (X.0.0): Breaking changes, major rewrites
- **Minor version** (2.X.0): New features, backward compatible
- **Patch version** (2.0.X): Bug fixes, minor improvements

---

## Upgrading

### From 1.x to 2.0

Version 2.0 is a complete rewrite and is **not backward compatible** with 1.x.

**Migration steps:**
1. Read the new documentation in `docs/`
2. Review API changes (see `docs/GENES-AI-FRAMEWORK.md`)
3. Update database schema to use universal 5-table design
4. Rewrite function calls to use new registry pattern (`g::run()`)
5. Update frontend code to use new JavaScript API
6. Test thoroughly before deploying

**Key API changes:**
- `db::select()` → `g::run("db.select")`
- Old auth system → New `auth` module
- Template syntax updated
- New CSS class naming conventions

---

## Support

For questions about changes or upgrade assistance:
- Open an issue: https://github.com/devrimvardar/genes/issues
- Join discussions: https://github.com/devrimvardar/genes/discussions
