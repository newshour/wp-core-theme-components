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
        $baseDir = getcwd();

        if ($baseDir === false) {
            error_log('Could not determine the current working directory.');
            exit;
        }

        $appEnv = rtrim($baseDir, '/') . '/config/application.php';

        if (!file_exists($appEnv)) {
            error_log('Could not find config/application.php at the project root.');
            exit;
        }

        include_once $appEnv;

        return CoreThemeKernel::create('test', WP_DEBUG);
    }
}
