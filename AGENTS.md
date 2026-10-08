# Project: teknoo/bridge-phpdi-symfony

## Overview
This package provides a seamless integration for [PHP-DI](http://php-di.org) within a Symfony application. It acts as a
Symfony Bundle that integrates PHP-DI into the Symfony Container, using PHP-DI entries as factories for Symfony entries.

Unlike the official Symfony-PHP-DI bridge, this implementation:
- Does not require a custom version of the Symfony Kernel or the Symfony Container.
- During Symfony container compilation, all entries in PHP-DI are referenced into the Symfony Container.
- Implements the `PSR-11` Container interface to facilitate interaction between PHP-DI and the Symfony Container.
- Automatically manages Symfony's parameters within the PHP-DI context.

## Key Features
- **Seamless Integration**: Integrates PHP-DI entries into Symfony without kernel overrides.
- **PSR-11 Compliance**: Acts as an interface between the two containers.
- **Configurable Compilation**: Supports PHP-DI container compilation to a specific path (`di_bridge.compilation_path`).
- **Caching**: Option to enable/disable PHP-DI's definitions cache (`di_bridge.enable_cache`, requires APCu).
- **Ordered definitions**: `di_bridge.definitions` entries accept a `priority` (highest loaded first).
- **Alias Management**: `di_bridge.import` creates aliases from Symfony container entries into PHP-DI.
- **Extensions**: `di_bridge.extensions` lists `Teknoo\DI\SymfonyBridge\Extension\ExtensionInterface` implementations
  (by class name or Symfony service id, with optional `priority`) which complete the bridge configuration.

## Architecture
- `src/DependencyInjection/Configuration.php`: configuration tree of the `di_bridge` key.
- `src/DependencyInjection/DIBridgeExtension.php`: Symfony extension. `prepend()` fetches extensions declared by service
  id from the application's container (Symfony passes an empty temporary container to `load()`), `load()` builds a
  `BridgeBuilder` and runs it.
- `src/Container/BridgeBuilder.php`: runs at Symfony compile time. Builds the PHP-DI container (`BridgeTrait`), then
  converts every PHP-DI definition into Symfony definitions (services with the `Bridge` as factory), aliases or
  parameters. See the README section "How PHP-DI entries are exported into Symfony" for the rules.
- `src/Container/Bridge.php`: runtime PSR-11 bridge, registered as a private Symfony service. It is the factory of
  every exported service, and the wrapper container of PHP-DI (PHP-DI resolves unknown entries, and Symfony's
  parameters, through it).
- `src/Container/Container.php`, `CompiledContainer.php`, `ContainerDefinitionTrait.php`: PHP-DI containers exposing
  their definitions (`extractDefinition()`) to the builder.

## Requirements
### PHP
- PHP 8.4+ (`composer.json` requires `^8.4`; CI runs 8.4 with lowest dependencies and 8.5 with latest).
- Keep the code compatible with PHP 8.4, do not use PHP 8.5 only features.

### Dependencies
- `php-di/php-di` ^7.1.1 and `php-di/invoker` ^2.2.0 (older releases use implicit nullable parameters, deprecated
  since PHP 8.4)
- `symfony/dependency-injection`, `symfony/http-kernel`, `symfony/config` ^6.4.24 || ^7.4 || ^8.1

## Development Workflow

### Dependencies
To install or update dependencies:
```bash
make depend
```
`DEPENDENCIES=lowest make depend` installs the lowest supported versions (as in CI).

### Quality Assurance
To run all QA checks (linting, PHPStan level max, PHPCS PSR-12, and security audit):
```bash
make qa
```
`make qa-offline` skips the audit (needs network).

Specific checks:
- **Static Analysis**: `make phpstan`
- **Code Style**: `make phpcs` (PSR-12 on `src/`)
- **Security Audit**: `make audit`
- **Syntax Check**: `make lint`

`rector.php` is provided but Rector is not in `require-dev`, run it with a global installation if needed.

### Testing
All functional and unit tests must be executed to verify changes:
```bash
make test
```
`make test` requires the `xdebug` extension (coverage) and the `apcu` extension (it runs PHP with `apc.enable_cli=1`,
cache related tests are skipped without APCu). Without coverage:
```bash
php -dapc.enable_cli=1 vendor/bin/phpunit -c phpunit.xml --no-coverage
```

### Cleanup
To remove installed dependencies:
```bash
make clean
```

## Project Structure
- `src/`: Core logic for the PHP-DI and Symfony bridge.
- `tests/UnitTest/`: PHPUnit unit tests, with stubs/mocks of PHP-DI and Symfony builders. Support classes live in
  `Support/` sub folders.
- `tests/FunctionalTest/`: PHPUnit functional tests booting a real Symfony kernel (`Fixtures/Kernel.php`) with the
  configuration files in `Fixtures/config/*.yml` and PHP-DI definitions in `Fixtures/config/di*.php`.
  `AbstractFunctionalTests::createKernel('file.yml')` clears the cache and boots the kernel.

## Rules for changes
- Test files must be named `*Test.php` (phpunit.xml discovers this suffix only), one class per file.
- Every bug fix or behavior change must add new unit tests and functional tests reproducing the issue. Do not modify
  existing tests: a failing existing test means a regression, unless the test itself was wrong (ask before changing it).
- Keep the public API and the existing behaviors stable, one commit per fix.
- Run `make qa-offline` and the tests before each commit, update `CHANGELOG.md`.

## Maintainers
- **Lead Developer**: Richard Déloge (Software Architect)
- **Original Author**: Matthieu Napoli
