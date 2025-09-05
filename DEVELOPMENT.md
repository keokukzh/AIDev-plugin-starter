# AIDevelopment Release & Test Suite

## Setup
- PHP + Composer, Node.js
- `composer install`
- `npm install`
- Lokal-WP: `npx @wordpress/env start` (optional)

## Qualität
- PHPCS: `composer run lint` / Fix: `composer run lint:fix`
- PHPStan: `composer run stan`
- PHPUnit: `composer run test`
- E2E: `npm run e2e` (mit `E2E_BASE_URL` oder wp-env)

## Release Flow
- Conventional Commits (feat:, fix:, chore:, docs:, refactor:, perf:, test:)
- Push auf `main` → **semantic-release** erstellt Release + CHANGELOG
- Tag `vX.Y.Z` → optionaler **WordPress.org Deploy**

## CI Secrets
- `GITHUB_TOKEN` (auto)
- `E2E_BASE_URL` (URL deiner Testinstanz)
- `WPORG_SVN_USERNAME` / `WPORG_SVN_PASSWORD` (optional)
