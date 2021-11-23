<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Contexts;

use NewsHour\WPCoreThemeComponents\Contexts\BaseContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class BaseContextTest extends TestCase
{
    /**
     * @return void
     */
    public function testToString(): void
    {
        $context = new BaseContext(Request::createFromGlobals());
        $this->assertIsString(strval($context));
    }

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $context = new BaseContext(Request::createFromGlobals());
        $this->assertIsArray($context->toArray());
    }

    /**
     * @return void
     */
    public function testCount(): void
    {
        $context = new BaseContext(Request::createFromGlobals(), ['foo' => 'bar']);
        $this->assertGreaterThan(0, count($context));
    }

    /**
     * @return void
     */
    public function testGetRequest(): void
    {
        $context = new BaseContext(Request::createFromGlobals());
        $this->assertInstanceOf(Request::class, $context->getRequest());
    }
}
