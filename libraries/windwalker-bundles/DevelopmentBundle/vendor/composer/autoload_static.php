<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1960c154de09b23d24b839e190023196
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Faker\\' => 6,
        ),
        'D' => 
        array (
            'DevelopmentBundle\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Faker\\' => 
        array (
            0 => __DIR__ . '/..' . '/fzaninotto/faker/src/Faker',
        ),
        'DevelopmentBundle\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1960c154de09b23d24b839e190023196::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1960c154de09b23d24b839e190023196::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
