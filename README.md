Teknoo Software - PHP-DI integration with Symfony
=================================================

[![Latest Stable Version](https://poser.pugx.org/teknoo/bridge-phpdi-symfony/v/stable)](https://packagist.org/packages/teknoo/bridge-phpdi-symfony)
[![Latest Unstable Version](https://poser.pugx.org/teknoo/bridge-phpdi-symfony/v/unstable)](https://packagist.org/packages/teknoo/bridge-phpdi-symfony)
[![Total Downloads](https://poser.pugx.org/teknoo/bridge-phpdi-symfony/downloads)](https://packagist.org/packages/teknoo/bridge-phpdi-symfony)
[![License](https://poser.pugx.org/teknoo/bridge-phpdi-symfony/license)](https://packagist.org/packages/teknoo/bridge-phpdi-symfony)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

This package provides integration for PHP-DI with Symfony. [PHP-DI](http://php-di.org) is a dependency injection container for PHP.
This bridge works as Symfony Bundle to integrate PHP-DI into the Symfony Container, as factory for entries defined into PHP-DI.
Unlike the official bridge, this bridge does not require to use a custom version of Symfony's Kernel, neither a custom version of
Symfony's container.
During Symfony container's compilation, all entries in PHP-DI are referenced into Symfony's Container.
The bridge also implements the PSR Container interface *`(PSR-11)`* to act as an interface with Symfony Container in
PHP-DI factories: they directly call the Symfony Container instead of PHP-DI. The bridge also automatically manages the
Symfony's parameters, managed differently by Symfony.

Install this bridge
-------------------
**If you use a previous version of PHP-DI Bridge, remove PHP-DI Kernel overload and use the default kernel**

* Add to you `bundles.php` file 

        Teknoo\DI\SymfonyBridge\DIBridgeBundle::class => ['all' => true],

* Create the file `di_bridge.yaml` in your config folder and put in

        di_bridge:
          #To enable PHP-DI's container compilation (disable by default)
          #Use a path under the Symfony's cache dir, to purge the compiled container with `cache:clear`
          compilation_path: ~ #Default, or path to store cache, like '%kernel.cache_dir%/phpdi'
          #To enable PHP-DI's definitions cache (disable by default, requires the APCu extension)
          enable_cache: false #Default or true
          definitions:
            - 'list of PHP-DI definitions file, you can use Symfony joker like %kernel.project_dir%'
            #example
            - '%kernel.project_dir%/vendor/editor_name/package_name/src/di.php'
            #files with a higher priority are loaded first (default priority is 0)
            - { file: '%kernel.project_dir%/config/di.php', priority: 10 }
          import:
            #To make alias from SF entries into PHPDI
            My\Class\Name: 'symfony.container.entry.name'
          extensions:
            #Optional, extensions (implementing Teknoo\DI\SymfonyBridge\Extension\ExtensionInterface)
            #to complete the bridge's configuration, by class name or by Symfony service id
            - 'App\DI\MyExtension'
            - { name: 'app.di.extension', priority: 10 }

How PHP-DI entries are exported into Symfony
--------------------------------------------
During the Symfony container compilation, the bridge builds the PHP-DI container and registers each PHP-DI entry into
Symfony:

* Objects (`DI\create()`, `DI\autowire()`, `DI\factory()`, `DI\decorate()`, class names, `DI\value()` with an object)
  become public Symfony services, built by PHP-DI through the bridge. For factories, the Symfony's service class is
  read from the return type of the callable, all PHP-DI's callable forms are supported.
* `DI\get()` references become Symfony aliases.
* Scalars, enums, strings (`DI\string()`), arrays of scalars and environment variables (`DI\env()`) become Symfony
  parameters. Percent signs are escaped, a PHP-DI value like `'50%'` or `'%foo%'` stays literal in Symfony (use
  `DI\get('foo')` or `DI\string('{foo}')` to reference another entry). `DI\string()` expressions are resolved only
  by PHP-DI, Symfony receives the raw expression.
* The default value of `DI\env()` can be a scalar, an array, or a `DI\get()` reference to an entry exported as a
  parameter (or an existing Symfony parameter).
* Arrays holding objects or nested definitions (like `['handlers' => [DI\get(A::class)]]`) become private Symfony
  services returning an array, resolved by PHP-DI. They can be injected as arguments of Symfony services, but can not
  be fetched with `$container->get()` (Symfony services must be objects).
* PHP-DI's internal entries (`Psr\Container\ContainerInterface`, `DI\Container`, `DI\FactoryInterface`,
  `Invoker\InvokerInterface`) are not exported. The bridge is registered as a private
  `Teknoo\DI\SymfonyBridge\Container\Bridge` service, alias it if you need it from Symfony.
* Private Symfony services are reachable from PHP-DI definitions only through the aliases declared in `import`
  (`DI\get('symfony.private.service')` fails at runtime, `DI\get('imported_alias')` works).
* Extensions declared by a Symfony service id are fetched when the bundle's configuration is prepended: the service
  must be defined by the application's configuration files (or by a `Bundle::build()`), not by another bundle's
  extension.

Support this project
---------------------
This project is free and will remain free. It is fully supported by commercial activities of SASU Teknoo Software
and EIRL Richard DELOGE. If you like it and help me maintain it and evolve it, don't hesitate to support me on
[Patreon](https://patreon.com/teknoo_software) or [Github](https://github.com/sponsors/TeknooSoftware).

Thanks :) Richard.

Credits
-------
EIRL Richard Déloge - <https://deloge.io> - Lead developer.
SASU Teknoo Software - <https://teknoo.software>

About Teknoo Software
---------------------
**Teknoo Software** is a PHP software editor, founded by Richard Déloge, as part of EIRL Richard Déloge.
Teknoo Software's goals : Provide to our partners and to the community a set of high quality services or software,
sharing knowledge and skills.

License
-------
This library is licensed under the 3-Clause BSD License - see the LICENSE file for details.

Installation & Requirements
---------------------------
To install this library with composer, run this command :

    composer require teknoo/bridge-phpdi-symfony

This library requires :

    * PHP 8.4+
    * A PHP autoloader (Composer is recommended)
    * PHP-DI 7.1+
    * Symfony/dependency-injection 6.4, 7.4 or 8.1+
    * Symfony/http-kernel 6.4, 7.4 or 8.1+
    * Symfony/config 6.4, 7.4 or 8.1+
    * The APCu extension, to enable PHP-DI's definitions cache

Contribute :)
-------------
You are welcome to contribute to this project. [Fork it on Github](CONTRIBUTING.md)
