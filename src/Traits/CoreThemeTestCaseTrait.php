<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Traits;

use NewsHour\WPCoreThemeComponents\CoreThemeKernel;

trait CoreThemeTestCaseTrait
{
    /**
     * Boots the CoreThemeKernel for testing.
     *
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

        $environment = $options['environment'] ?? 'test';
        $debug = $options['environment'] ?? WP_DEBUG;

        return CoreThemeKernel::create($environment, $debug);
    }
}
