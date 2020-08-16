<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit07323f5502d8443a84b00c1772dbf8de
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'App\\DBHelper\\DBHelper' => __DIR__ . '/../..' . '/classes/DB/DBHelper.class.php',
        'App\\DB\\DB' => __DIR__ . '/../..' . '/classes/DB/DB.class.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit07323f5502d8443a84b00c1772dbf8de::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit07323f5502d8443a84b00c1772dbf8de::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit07323f5502d8443a84b00c1772dbf8de::$classMap;

        }, null, ClassLoader::class);
    }
}
