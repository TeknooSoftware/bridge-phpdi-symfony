#Teknoo Software - PHP-DI integration with Symfony - Change Log

##[4.0.0-beta1] - 2020-09-08
##Beta Release
###Remove
- Symfony Kernel overloading
- Symfony Container overloading

###Change
- This integrated is used as Symfony Bundle and not a Symfony Kernel overloading.
- Will can be managed with Symfony Flex.
- PHP-DI is used as Symfony's services factory (thanks to a bridge) 
  and instantiate by Symfony Container.
- All entries in PHP-DI are referenced into Symfony Container during compilation, 
  with PHP-DI as factory of theses entries.
  
###Add
- DIBridgeBundle to automate the integration
- DIBridgeExtension to transfert to Yaml configuration PHP-DI files instead 
  of into Kernel
- Container/BridgeBuilder to register all entries from PHP-DI into Symfony 
  Container, with PHP-DI as Factory
- Container/Container, extension of PHP-DI to allow BridgeBuilder to work
- Container/Bridge to manage Symfony Container's call to PHP-DI as Factory
  and Bridge to Symfony Container and PHP-DI from PHP-DI's definitions 
  (Parameters are not accessible directly via has/get method in Symfony)

