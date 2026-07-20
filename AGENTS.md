# Project: teknoo/bridge-phpdi-symfony

## Overview
This package provides a seamless integration for [PHP-DI](http://php-di.org) within a Symfony application. It acts as a Symfony Bundle that integrates PHP-DI into the Symfony Container, using PHP-DI entries as factories for Symfony entries.

Unlike the official Symfony-PHP-DI bridge, this implementation:
- Does not require a custom version of the Symfony Kernel or the Symfony Container.
- During Symfony container compilation, all entries in PHP-DI are referenced into the Symfony Container.
- Implements the `PSR-11` Container interface to facilitate interaction between PHP-DI and the Symfony Container.
- Automatically manages Symfony's parameters within the PHP-DI context.

## Key Features
- **Seamless Integration**: Integrates PHP-DI entries into Symfony without kernel overrides.
- **PSR-11 Compliance**: Acts as an interface between the two containers.
- **Configurable Compilation**: Supports PHP-DI container compilation to a specific path.
- **Caching**: Option to enable/disable PHP-DI's internal caching.
- **Alias Management**: Allows creating aliases from Symfony container entries into PHP-DI.

## Requirements
### PHP
- PHP 8.1+ (requires 8.4 for current development)

### Dependencies
- `php-di/php-di`
- `symfony/dependency-injection`
- `symfony/http-kernel`
- `symfony/config`

## Development Workflow

### Dependencies
To install or update dependencies:
```bash
make depend
```

### Quality Assurance
To run all QA checks (linting, PHPStan, PHPCS, and security audit):
```bash
make qa
```

Specific checks:
- **Static Analysis**: `make phpstan`
- **Code Style**: `make phpcs`
- **Security Audit**: `make audit`
- **Syntax Check**: `make lint`

### Testing
All functional and unit tests must be executed to verify changes:
```bash
make test
```

### Cleanup
To remove installed dependencies:
```bash
make clean
```

## Project Structure
- `src/`: Core logic for the PHP-DI and Symfony bridge.
- `tests/`: Unit and functional tests.

## Maintainers
- **Lead Developer**: Richard Déloge (Software Architect)
- **Original Author**: Matthieu Napoli
