<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CoreThemeTestCase extends KernelTestCase
{
    /**
     * @param array $options
     * @return KernelInterface
     */
    protected static function createKernel(array $options = [])
    {
        include_once getcwd() . '/config/application.php';

        return CoreThemeKernel::create('test', WP_DEBUG);
    }
}