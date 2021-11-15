<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Containers;

use NewsHour\WPCoreThemeComponents\Components\Meta\MetaFactory;
use NewsHour\WPCoreThemeComponents\Components\Meta\Schemas\SchemaFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Request;

use NewsHour\WPCoreThemeComponents\Contexts\Context;
use NewsHour\WPCoreThemeComponents\Contexts\ContextFactory;
use NewsHour\WPCoreThemeComponents\Contexts\PageContext;
use NewsHour\WPCoreThemeComponents\Contexts\PostContext;
use NewsHour\WPCoreThemeComponents\Http\Factories\RequestFactory;

/**
 * @param ContainerConfigurator $configurator
 */
return function (ContainerConfigurator $configurator) {

    $services = $configurator->services()->defaults()->autowire();

    // Use factory class for Request objs.
    $services->set(Request::class)->factory([RequestFactory::class, 'get']);

    // Contexts.
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
    )->public();

};
