# Teknoo Software - PHP-DI integration with Symfony - Change Log

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

