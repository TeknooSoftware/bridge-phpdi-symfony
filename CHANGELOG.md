# Teknoo Software - PHP-DI integration with Symfony - Change Log

## [6.0.6] - 2024-05-31
### Stable Release
- Fix deprecated : replace `Symfony\Component\HttpKernel\DependencyInjection\Extension`
        by `Symfony\Component\DependencyInjection\Extension\Extension`

## [6.0.5] - 2024-01-17
### Stable Release
- Default value for environments variables are also injected into the Symfony DI

## [6.0.4] - 2023-11-29
### Stable Release
- Support Symfony 7+

## [6.0.3] - 2023-11-29
### Stable Release
- Update dev lib requirements
- Support Symfony 7+

## [6.0.2] - 2023-11-29
### Stable Release
- Fix false positive in PhpStan

## [6.0.1] - 2023-09-20
### Stable Release
- Improve error message to simplify debug 

## [6.0.0] - 2023-07-13
### Stable Release
- Support PHP-DI 7.0 and later
- Drop support of Symfony 5.4

## [6.0.0-beta1] - 2023-07-12
### Beta Release
- Support PHP-DI 7.0 and later
- Drop support of Symfony 5.4

## [5.2.6] - 2023-06-07
### Stable Release
- Require Symfony 5.4 or 6.3 or newer

## [5.2.5] - 2023-05-15
### Stable Release
- Update dev lib requirements
- Update copyrights

## [5.2.4] - 2023-04-16
### Stable Release
- Update dev lib requirements
- Support PHPUnit 10.1+
- Migrate phpunit.xml

## [5.2.3] - 2023-03-12
### Stable Release
- Q/A

## [5.2.2] - 2023-02-11
### Stable Release
- Remove phpcpd and upgrade phpunit.xml and fix removed useful features in PHPUnit 10

## [5.2.1] - 2023-02-03
### Stable Release
- Update dev libs to support PHPUnit 10 and remove unused phploc

## [5.2.0] - 2022-12-16
### Stable Release
- Fix some QA
- Drop Support of PHP 8.0
- Keep Support of LTS Symfony 5.4. Symfony 6.0 is not longuer supported but not forbidded in
  composer.json to help upgrade fron Symfony 5.4

## [5.1.2] - 2022-08-06
### Stable Release
- Update composer.json

## [5.1.1] - 2022-06-17
### Stable Release
- Update dev libs requirements

## [5.1.0] - 2022-04-15
### Stable Release
- Add `priority` key to `di_bridge.definitions` list to sort definitions
- `Bridge` container accepts now an `iterable<string>` for `$definitionsFiles` instead of `array<string>`

## [5.0.7] - 2021-12-12
### Stable Release
- Remove unused QA tool
- Remove support of Symfony 5.3
- Support Symfony 5.4 and 6.0+

## [5.0.6] - 2021-12-03
### Stable Release
- Fix some deprecated with PHP 8.1

## [5.0.5] - 2021-11-12
### Stable Release
- Switch to PHPStan 1.1+

## [5.0.4] - 2021-08-12
### Stable Release
- Remove support of Symfony 5.2

## [5.0.3] - 2021-07-05
### Stable Release
- Update documents and dev libs requirements

## [5.0.2] - 2021-05-31
### Stable Release
- Minor version about libs requirements

## [5.0.1] - 2021-03-24
### Stable Release
- Constructor Property Promotion
- Non-capturing catches

## [5.0.0] - 2021-03-20
### Stable Release
- Migrate to PHP 8.0
- Remove support of Symfony 4.4
- Fix license header
- QA

## [4.1.1] - 2021-02-09
### Stable Release
- Exception of BridgeBuilder display the Definition's name, and it is more explicit when the return type missing

## [4.1.0] - 2021-01-24
### Stable Release
- Add support of PHP-DI's compilation, disable by default
- Add support of PHP-DI's cache, disable by default
- Create `Teknoo\DI\SymfonyBridge\Container\CompiledContainer` to wrap a compiled PHP-DI Container to use in Symfony
- Create `Teknoo\DI\SymfonyBridge\Container\ContainerInterface`, implemented by `Container` and `CompiledContainer` to 
  abstract them into the bridge.
- Adapt `Bridge`, and `BridgeBuilder` to use `CompiledContainer`
- Add Symfony option `di_bridge.compilation_path`
- Add Symfony option `di_bridge.enable_cache`

## [4.0.4] - 2020-12-03
### Stable Release
- Official Support of PHP8

## [4.0.3] - 2020-10-12
### Stable Release
- Prepare library to support also PHP8.
- Complete coverage

## [4.0.2] - 2020-09-18
### Stable Release
- Update QA and CI tools

## [4.0.1] - 2020-09-1!
## Stable Release
### Fix
- Fix Configurator when there are several di_bridge.import in the configuration 

## [4.0.0] - 2020-09-10
## Stable Release
### First Release
### Remove
- Symfony Kernel overloading
- Symfony Container overloading
- Autowiring of non registered class from PHP-DI. (Use Symfony's behavior instead).

### Change
- This integrated is used as Symfony Bundle and not a Symfony Kernel overloading.
- Will can be managed with Symfony Flex.
- PHP-DI is used as Symfony's services factory (thanks to a bridge) 
  and instantiate by Symfony Container.
- All entries in PHP-DI are referenced into Symfony Container during compilation, 
  with PHP-DI as factory of theses entries.
  
### Add
- DIBridgeBundle to automate the integration
- DIBridgeExtension to transfert to Yaml configuration PHP-DI files instead 
  of into Kernel
- Container/BridgeBuilder to register all entries from PHP-DI into Symfony 
  Container, with PHP-DI as Factory
- Container/Container, extension of PHP-DI to allow BridgeBuilder to work
- Container/Bridge to manage Symfony Container's call to PHP-DI as Factory
  and Bridge to Symfony Container and PHP-DI from PHP-DI's definitions 
  (Parameters are not accessible directly via has/get method in Symfony)

## [4.0.0-beta4] - 2020-09-10
## Beta Release
### Fix
- Fix transposition of Array parameters from PHPDI to Symfony (Recursive conversion of ArrayDefinition to array)

## [4.0.0-beta3] - 2020-09-09
## Beta Release
### Update
- QA
 
## [4.0.0-beta2] - 2020-09-09
## Beta Release
### Update
- Functionals tests and unit test, Increase coverage

### Fix
- Several bug in DIs interaction 

## [4.0.0-beta1] - 2020-09-08
## Beta Release
### Remove
- Symfony Kernel overloading
- Symfony Container overloading
- Autowiring of non registered class from PHP-DI. (Use Symfony's behavior instead).

### Change
- This integrated is used as Symfony Bundle and not a Symfony Kernel overloading.
- Will can be managed with Symfony Flex.
- PHP-DI is used as Symfony's services factory (thanks to a bridge) 
  and instantiate by Symfony Container.
- All entries in PHP-DI are referenced into Symfony Container during compilation, 
  with PHP-DI as factory of theses entries.
  
### Add
- DIBridgeBundle to automate the integration
- DIBridgeExtension to transfert to Yaml configuration PHP-DI files instead 
  of into Kernel
- Container/BridgeBuilder to register all entries from PHP-DI into Symfony 
  Container, with PHP-DI as Factory
- Container/Container, extension of PHP-DI to allow BridgeBuilder to work
- Container/Bridge to manage Symfony Container's call to PHP-DI as Factory
  and Bridge to Symfony Container and PHP-DI from PHP-DI's definitions 
  (Parameters are not accessible directly via has/get method in Symfony)

