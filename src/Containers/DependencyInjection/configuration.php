<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Containers;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Timber\Timber;
use NewsHour\WPCoreThemeComponents\Components\Meta\MetaFactory;
use NewsHour\WPCoreThemeComponents\Contexts\Context;
use NewsHour\WPCoreThemeComponents\Contexts\ContextFactory;
use NewsHour\WPCoreThemeComponents\Contexts\PageContext;
use NewsHour\WPCoreThemeComponents\Contexts\PostContext;
use NewsHour\WPCoreThemeComponents\Controllers\ControllerResolver;
use NewsHour\WPCoreThemeComponents\Http\Factories\RequestFactory;

/**
 * @param ContainerConfigurator $configurator
 */
return function (ContainerConfigurator $configurator) {
    $services = $configurator->services()->defaults()->autowire()->autoconfigure();

    $services->set('controller_resolver', ControllerResolver::class);
    $services->set('argument_resolver', ArgumentResolver::class);

    // Use factory class for Request objs.
    $services->set('request_stack', RequestFactory::class)->factory([RequestFactory::class, 'getStack']);
    $services->set(Request::class)->factory([RequestFactory::class, 'get']);
    $services->alias('request', Request::class);

    // Contexts.
    $services->set('timber.context', Timber::class)->factory([Timber::class, 'context']);
    $services->set(AjaxContext::class)->factory([ContextFactory::class, 'ajax']);
    $services->set(PageContext::class)->factory([ContextFactory::class, 'page']);
    $services->set(PostContext::class)->factory([ContextFactory::class, 'post']);
    $services->set(Context::class)->factory([ContextFactory::class, 'default']);

    // Metas.
    $services->set(MetaFactory::class)->factory([MetaFactory::class, 'instance']);

    // Automatically load the theme's controllers.
    $services->load(
        'App\\Themes\\CoreTheme\\Http\\Controllers\\',
        trailingslashit(get_template_directory()) . 'src/Http/Controllers/*'
    )->tag('controller.service_arguments')
     ->public();

    // Automatically load the theme's commands.
    $cmdsDir = trailingslashit(get_template_directory()) . 'src/Commands';

    if (is_dir($cmdsDir)) {
        $cmdsFinder = new Finder();
        $cmdsFinder->files()->in($cmdsDir);

        if ($cmdsFinder->hasResults()) {
            $services->load(
                'App\\Themes\\CoreTheme\\Commands\\',
                $cmdsDir . '/*'
            )->public();
        }
    }
};
