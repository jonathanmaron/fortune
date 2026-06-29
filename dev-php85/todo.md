# PHP 8.3 → 8.5 migration — fortune

Tracking checklist for the `php85` branch migration. Mirrors the finished
`tismailer` migration conventions (composer scripts, ecs.php, rector.php,
phpstan.neon).

## composer.json

- [x] `php` constraint → `^8.5`
- [x] `ctw/*` packages → `dev-php85` (`ctw/ctw-qa`)
- [x] `textcontrol/*` packages → `dev-php85` (none present in this app)
- [x] `config.platform.php` → `8.5.7` (not set in this app — N/A)
- [x] `scripts` block aligned exactly to tismailer (incl. self-contained `test`)
- [x] `phpunit/phpunit` → `^13.0` (added so `composer test` is self-contained)
- [x] Remove redundant/conflicting `phpstan/phpstan-phpunit` (not present — N/A)
- [x] No new packages except the abandoned/EOL replacement
- [x] `ecs.php` → tismailer IIFE / ECSConfigBuilder format, paths adjusted
- [x] `rector.php` → tismailer callback style, paths adjusted
- [x] `phpstan.neon` → includes ctw-qa common.neon + phpstan-baseline.neon
- [x] `phpstan-baseline.neon` kept empty (no suppressions)
- [x] `phpunit.xml.dist` modeled on tismailer, paths adjusted

## Abandoned

- [x] `composer audit --abandoned=report` clean
- [x] `riimu/kit-phpencoder` 2.4.2 (unmaintained, carried an implicit-nullable
      PHP 8.5 deprecation) replaced by the org satis fork `^3.0` (3.0.7,
      `require: php ^8.5`, explicit `?array $encoders`). Same namespace and
      `PHPEncoder` API — no application code change required.

## Runtime + deprecations

- [x] `composer update` succeeds on PHP 8.5
- [x] Runtime smoke: `bin/fortune list` + `help <cmd>` clean (no deprecations)
- [x] Fixed `CommandFactory::__invoke()` implicit-nullable `$container`
      (`?ContainerInterface`)
- [x] Removed three forbidden `@phpstan-ignore-next-line` from `IndexCommand`
      (variable-variables refactored to explicit index assignment)
- [x] `composer test` — zero deprecations / warnings, green
- [x] PHPStan at max level — zero issues (no baseline, no ignores; data-load
      boundaries typed with house-style `assert()` narrowing)
- [x] `composer qa-fix` → ECS "No errors", PHPStan "No issues found"

## Finalise

- [x] Tick this todo
- [x] Commit in logical units (conventional commit + emoji)
- [x] Push `php85`
- [x] Confirm tree clean + pushed

## Notes

- fortune ships **no test suite** on master. To honor the playbook DoD
  ("tests pass") and a self-contained `composer test`, a minimal smoke-test
  suite was added under `test/` (boots the console Application, exercises the
  read-only `Fortune` domain class and the `fortune`/`statistics` commands).
  The data-mutating commands (`import`, `index`, `purge`) are intentionally
  not exercised so tracked `data/` files are never rewritten.
- Entrypoint is `bin/fortune` (default command `fortune`), not
  `bin/console.php`.
- No `config/` directory; the autoloaded root files are `bootstrap.php` and
  `consts.php`, which stand in for tismailer's `config/` in the QA paths.
