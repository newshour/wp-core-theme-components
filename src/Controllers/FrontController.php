<?php
/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Controllers;

use Exception;
use ReflectionClass;
use ReflectionException;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\PsrCachedReader;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;

use NewsHour\WPCoreThemeComponents\Annotations\HttpMethods;
use NewsHour\WPCoreThemeComponents\Annotations\LoginRequired;
use NewsHour\WPCoreThemeComponents\Containers\ContainerFactory;
use NewsHour\WPCoreThemeComponents\Contexts\Context;
use NewsHour\WPCoreThemeComponents\Http\Factories\RequestFactory;
use Symfony\Component\HttpFoundation\Response;

/**
 * Loads controller classes from the Wordpress "template" files. e.g. single.php,
 * single-some-post-type.php, page.php, etc.
 *
 * @final
 */
final class FrontController {

    /**
     * Loads a controller and invokes the method. An optional context object can
     * be passed. If one is not, the default context defined in ContextFactory
     * is loaded.
     *
     * @param  string  $className
     * @param string $method
     * @param  Context $context Deprecated
     * @return Controller
     */
    public static function run(string $controllerClass, string $method, Context $context = null) {

        try {

            $reflectedClass = new ReflectionClass($controllerClass);

            if (!$reflectedClass->isInstantiable() || !$reflectedClass->isSubclassOf(Controller::class)) {
                trigger_error(
                    sprintf(
                        'Error: <b>%s</b> is not a valid Controller.',
                        $controllerClass
                    ),
                    E_USER_ERROR
                );
            }

            if (!$reflectedClass->hasMethod($method)) {
                trigger_error(
                    sprintf(
                        'A method named <b>%s</b> could not be found in <i>%s</i>.',
                        $method,
                        $controllerClass
                    ),
                    E_USER_ERROR
                );
            }

            // Start processing annotations.
            AnnotationRegistry::registerLoader('class_exists');
            $debug = defined('WP_DEBUG') ? WP_DEBUG : false;

            $reader = new PsrCachedReader(
                new AnnotationReader(),
                new PhpArrayAdapter(
                    dirname(__DIR__, 2) . '/cache/app/annotations.php',
                    new FilesystemAdapter()
                ),
                $debug
            );

            // LoginRequired annotation.
            $loginRequired = $reader->getMethodAnnotation(
                $reflectedClass->getMethod($method),
                LoginRequired::class
            );

            if ($loginRequired !== null && !$loginRequired->validateUser()) {
                \wp_die(
                    '403 Access Forbidden',
                    'Error',
                    ['response' => 403]
                );
            }

            // HttpMethods annotation.
            $httpMethods = $reader->getMethodAnnotation(
                $reflectedClass->getMethod($method),
                HttpMethods::class
            );

            $request = RequestFactory::get();
            $allowed = $request->isMethodSafe();

            // Default are "safe" HTTP methods. Allow only if annotation value defines unsafe methods it.
            if ($httpMethods !== null) {
                $allowed = $httpMethods->validateMethods($request);
            }

            if (!$allowed) {
                \wp_die(
                    '405 Method Not Allowed',
                    'Error',
                    ['response' => 405]
                );
            }

            // Apply container filters.
            $container = apply_filters('core_theme_container', ContainerFactory::get());

            // Run the controller and get the Response obj.
            $response = call_user_func([
                $container->get($controllerClass),
                $method
            ]);

            if (!($response instanceof Response)) {
                \wp_die(
                    sprintf(
                        '<b>%s</b> did not return a Response object. Did you forget the <i>return</i> statement?',
                        $controllerClass . '::' . $method
                    ),
                    'Error',
                    ['response' => 400]
                );
            }

            // Apply any post controller action filters.
            $response = apply_filters('core_theme_response', $response);

            // Send the response.
            $response->send();

            // We're all done. Wordpress will run its `shutdown` action on exit.
            exit;

        } catch (ReflectionException $re) {

            trigger_error(
                sprintf('%s [stack trace] %s', $re->getMessage(), $re->getTraceAsString()),
                E_USER_ERROR
            );

        } catch (Exception $e) {

            trigger_error(
                sprintf('%s [stack trace] %s', $e->getMessage(), $e->getTraceAsString()),
                E_USER_ERROR
            );

        }

    }

}
