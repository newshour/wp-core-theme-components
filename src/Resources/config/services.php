<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Resources\config;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Timber\Timber;
use NewsHour\WPCoreThemeComponents\Components\Meta\MetaFactory;
use NewsHour\WPCoreThemeComponents\Contexts\Context;
use NewsHour\WPCoreThemeComponents\Contexts\ContextFactory;
use NewsHour\WPCoreThemeComponents\Contexts\PageContext;
use NewsHour\WPCoreThemeComponents\Contexts\PostContext;
use NewsHour\WPCoreThemeComponents\Controllers\ControllerResolver;
use NewsHour\WPCoreThemeComponents\Events\Listeners\ExceptionListener;
use NewsHour\WPCoreThemeComponents\Http\Factories\RequestFactory;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @param ContainerConfigurator $configurator
 */
return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    // Setup the Core Theme's controller resolver.
    $services->set('controller_resolver', ControllerResolver::class)->args([
        service('service_container'),
        service('logger')->ignoreOnInvalid(),
    ])->tag('monolog.logger', ['channel' => 'request']);

    // Setup exception listeners.
    $services->set('event_dispatcher', EventDispatcher::class)->public();
    $services->set(ExceptionListener::class)->args([
        service('logger')
    ])
    ->tag('kernel.event_listener', ['event' => 'kernel.exception'])
    ->tag('monolog.logger');

    $services->alias('exception_listener', ExceptionListener::class);

    // Use factory class for Request objs.
    $services->set('request_factory', RequestFactory::class);
    $services->set('request_stack', RequestStack::class)->factory([RequestFactory::class, 'getStack']);
    $services->set(Request::class)->factory([RequestFactory::class, 'get']);
    $services->alias('request', Request::class);

    // Contexts.
    $services->set('timber.context', Timber::class)->factory([Timber::class, 'context']);
    $services->set('context_factory', ContextFactory::class);
    $services->set(AjaxContext::class)->factory([service('context_factory'), 'ajax']);
    $services->set(PageContext::class)->factory([service('context_factory'), 'page']);
    $services->set(PostContext::class)->factory([service('context_factory'), 'post']);
    $services->set(Context::class)->factory([service('context_factory'), 'default']);

    // Metas.
    $services->set(MetaFactory::class)->factory([MetaFactory::class, 'instance']);
};
