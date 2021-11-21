<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use NewsHour\WPCoreThemeComponents\Annotations\HttpMethods;
use NewsHour\WPCoreThemeComponents\Annotations\LoginRequired;
use NewsHour\WPCoreThemeComponents\Http\Factories\RequestFactory;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class AnnotationsTest extends TestCase {

    /**
     * @return void
     */
    public function setup(): void {

        require_once 'mock_wp_functions.php';

        AnnotationRegistry::registerLoader('class_exists');

    }

    /**
     * @LoginRequired
     */
    public function loginRequiredAnnotatedMethod(): void {

        return;

    }

    /**
     * @HttpMethods("POST")
     */
    public function httpMethodsAnnotatedMethod(): void {

        return;

    }

    /**
     * Tests the HttpMethods annotation.
     *
     * @return void
     */
    public function testHttpMethods(): void {

        $request = RequestFactory::get();

        $reader = new AnnotationReader();
        $httpMethods = $reader->getMethodAnnotation(
            new ReflectionMethod(self::class, 'httpMethodsAnnotatedMethod'),
            HttpMethods::class
        );

        $request = RequestFactory::get();

        $this->assertIsObject($httpMethods);
        $this->assertFalse($httpMethods->validateMethods($request));

    }

    /**
     * Tests the LoginRequired annotation.
     *
     * @return void
     */
    public function testLoginRequired(): void {

        $reader = new AnnotationReader();
        $loginRequired = $reader->getMethodAnnotation(
            new ReflectionMethod(self::class, 'loginRequiredAnnotatedMethod'),
            LoginRequired::class
        );

        $this->assertIsObject($loginRequired);
        $this->assertFalse($loginRequired->validateUser());

    }

}
