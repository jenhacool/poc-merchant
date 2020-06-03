<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit244c05626e356b9a29ea134f00bd2713
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Tests\\' => 6,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Tests\\' => 
        array (
            0 => __DIR__ . '/../..' . '/tests',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static $classMap = array (
        'App\\POC_Merchant' => __DIR__ . '/../..' . '/app/class-poc-merchant.php',
        'App\\POC_Merchant_AJAX' => __DIR__ . '/../..' . '/app/class-poc-merchant-ajax.php',
        'App\\Utilities\\Helpers' => __DIR__ . '/../..' . '/app/utilities/Helpers.php',
        'App\\Utilities\\SingletonTrait' => __DIR__ . '/../..' . '/app/utilities/SingletonTrait.php',
        'Tests\\Helpers\\WC_Helper_Product' => __DIR__ . '/../..' . '/tests/helpers/class-wc-helper-product.php',
        'Tests\\Test_Class_POC_Merchant' => __DIR__ . '/../..' . '/tests/test-class-poc-merchant.php',
        'Tests\\Test_Class_POC_Merchant_AJAX' => __DIR__ . '/../..' . '/tests/test-class-poc-merchant-ajax.php',
        'Tests\\Test_POC_Merchant' => __DIR__ . '/../..' . '/tests/test-poc-merchant.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit244c05626e356b9a29ea134f00bd2713::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit244c05626e356b9a29ea134f00bd2713::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit244c05626e356b9a29ea134f00bd2713::$classMap;

        }, null, ClassLoader::class);
    }
}