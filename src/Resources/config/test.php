<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Resources\config;

use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\History;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\EventListener\SessionListener;

use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;

/**
 * @param ContainerConfigurator $configurator
 */
return static function (ContainerConfigurator $configurator) {
    $configurator->parameters()->set('test.client.parameters', []);

    $configurator->services()
        ->set('test.client', KernelBrowser::class)
        ->args([
            service('kernel'),
            param('test.client.parameters'),
            service('test.client.history'),
            service('test.client.cookiejar'),
        ])
        ->share(false)
        ->public()

        ->set('test.client.history', History::class)->share(false)
        ->set('test.client.cookiejar', CookieJar::class)->share(false)

        ->set('test.session.listener', SessionListener::class)
        ->args([
            service_locator([
                'session' => service('.session.do-not-use')->ignoreOnInvalid(),
                'session_factory' => service('session.factory')->ignoreOnInvalid(),
            ]),
            param('kernel.debug'),
            param('session.storage.options'),
        ])
        ->tag('kernel.event_subscriber')

        ->set('test.service_container', get_class($configurator))
        ->args([
            service('kernel'),
            'test.private_services_locator',
        ])
        ->public()

        ->set('test.private_services_locator', ServiceLocator::class)
        ->args([abstract_arg('callable collection')])
        ->public();
};
