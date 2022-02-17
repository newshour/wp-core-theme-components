<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Console;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

final class CoreThemeApplication extends Application
{
    /**
     * @return InputDefinition
     */
    protected function getDefaultInputDefinition(): InputDefinition
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(
            new InputOption(
                '--with-wordpress',
                null,
                InputOption::VALUE_NONE,
                'Load the Wordpress environment.'
            )
        );

        return $definition;
    }
}
