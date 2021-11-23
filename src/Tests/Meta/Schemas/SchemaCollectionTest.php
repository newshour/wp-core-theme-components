<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Meta\Schemas;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use NewsHour\WPCoreThemeComponents\Components\Meta\Schemas\OrganizationSchema;
use NewsHour\WPCoreThemeComponents\Components\Meta\Schemas\SchemaCollection;
use NewsHour\WPCoreThemeComponents\Tests\DummyController;

class SchemaCollectionTest extends TestCase
{
    private SchemaCollection $collection;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->collection = new SchemaCollection();
    }

    /**
     * @return void
     */
    public function testAttachException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->collection->attach(new DummyController());
    }

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $this->collection->attach(new OrganizationSchema());
        $this->collection->attach(new OrganizationSchema());

        $this->assertIsArray(
            $this->collection->toArray()
        );
    }
}
