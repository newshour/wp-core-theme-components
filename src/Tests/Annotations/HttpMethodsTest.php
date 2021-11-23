<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Annotations;

use ReflectionMethod;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use NewsHour\WPCoreThemeComponents\Annotations\HttpMethods;
use NewsHour\WPCoreThemeComponents\Http\Factories\RequestFactory;
use NewsHour\WPCoreThemeComponents\Tests\DummyController;

class HttpMethodsTest extends TestCase
{
    /**
     * @return void
     */
    public function setup(): void
    {
        require_once dirname(__DIR__) . '/mock_wp_functions.php';

        AnnotationRegistry::registerLoader('class_exists');
    }

    /**
     * @return void
     */
    public function testHttpMethods(): void
    {
        $reader = new AnnotationReader();
        $httpMethods = $reader->getMethodAnnotation(
            new ReflectionMethod(DummyController::class, 'postRequiredMethod'),
            HttpMethods::class
        );

        $request = RequestFactory::get();

        $this->assertIsObject($httpMethods);
        $this->assertFalse($httpMethods->validateMethods($request));
    }
}
