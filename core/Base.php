<?php
namespace core;

/**
 * Class Base
 */
class Base
{

    public static $classMap = [];
    
    public static $app;
    
    /*
     * autoloader all class
     */
    
    public static function autoload($className) {
        
            if (isset(static::$classMap[$className])) {
                    $classFile = static::$classMap[$className];
            } elseif (strpos($className, '\\') !== false) {
                    $classFile =  str_replace('\\', '/', $className) . '.php';
                    if ($classFile === false || !is_file($classFile)) {
                            return;
                    }
            } else {
                    return;
            }
            include($classFile); 
            if (!class_exists($className, false) && !interface_exists($className, false) && !trait_exists($className, false)) {
                     throw new Exception("Unable to find '$className' in file: $classFile. Namespace missing?");
            }
    }
    
        
}      
