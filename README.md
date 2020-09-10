Teknoo Software - PHP-DI integration with Symfony
=================================================

[![Build Status](https://travis-ci.com/TeknooSoftware/symfony-bridge.svg?branch=master)](https://travis-ci.com/TeknooSoftware/symfony-bridge)
[![Latest Stable Version](https://poser.pugx.org/teknoo/symfony-bridge/v/stable)](https://packagist.org/packages/teknoo/symfony-bridge)
[![Latest Unstable Version](https://poser.pugx.org/teknoo/symfony-bridge/v/unstable)](https://packagist.org/packages/teknoo/symfony-bridge)
[![Total Downloads](https://poser.pugx.org/teknoo/symfony-bridge/downloads)](https://packagist.org/packages/teknoo/symfony-bridge)
[![License](https://poser.pugx.org/teknoo/symfony-bridge/license)](https://packagist.org/packages/teknoo/symfony-bridge)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

This package provides integration for PHP-DI with Symfony. [PHP-DI](http://php-di.org) is a dependency injection container for PHP.
This bridge works as Symfony Bundle to integrate PHP-DI into the Symfony Container as factory for entries defined into PHP-DI.
Unlike the official bridge, this bridge does not require to use a custom version of Symfony's Kernel, neither a custom version of
Symfony's container.
During Symfony container's compilation, all entries in PHP-DI will be referenced into Symfony's Container.
The bridge will also implements the PSR Container interface *`(PSR-11)`* to act as an interface with Symfony Container in
PHP-DI factory.
They will directly call the Symfony Container instead of PHP-DI, The bridge will also automatically manage the management  
of the parameters, managed differently by Symfony.

Install this bridge
-------------------

**If you use a previous version of PHP-DI Bridge, remove PHP-DI Kernel overload and use the default kernel**

* Add to you `bundles.php` file 

        Teknoo\DI\SymfonyBridge\DIBridgeBundle::class => ['all' => true],

* Create the file `di_bridge.yaml` in your config folder and put in

        di_bridge:
              definitions:
                - 'list of PHP-DI definitions file, you can use Symfony joker like %kernel.project_dir%'
                #example
                - '%kernel.project_dir%/vendor/editor_name/package_name/src/di.php'
                - '%kernel.project_dir%/config/di.php'
              import:
                #To make alias from SF entries into PHPDI
                My\Class\Name: 'symfony.contaner.entry.name'


Support this project
---------------------

This project is free and will remain free, but it is developed on my personal time. 
If you like it and help me maintain it and evolve it, don't hesitate to support me on [Patreon](https://patreon.com/teknoo_software).
Thanks :) Richard. 

Installation & Requirements
---------------------------
To install this library with composer, run this command :

    composer require teknoo/bridge-phpdi-symfony

This library requires :

    * PHP 7.4+
    * A PHP autoloader (Composer is recommended)
    * PHP-DI.
    * Symfony/dependency-injection.
    * Symfony/http-kernel.
    * Symfony/config

Credits
-------
Matthieu Napoli - Original developer.
Richard Déloge - <richarddeloge@gmail.com> - Lead developer.
Teknoo Software - <https://teknoo.software>

About Teknoo Software
---------------------
**Teknoo Software** is a PHP software editor, founded by Richard Déloge.
Teknoo Software's goals : Provide to our partners and to the community a set of high quality services or software,
 sharing knowledge and skills.

License
-------
Symfony Bridge is licensed under the MIT License - see the licenses folder for details

Contribute :)
-------------

You are welcome to contribute to this project. [Fork it on Github](CONTRIBUTING.md)
