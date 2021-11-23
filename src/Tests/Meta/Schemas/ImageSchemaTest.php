<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Meta\Schemas;

use PHPUnit\Framework\TestCase;
use NewsHour\WPCoreThemeComponents\Components\Meta\Schemas\ImageSchema;

class ImageSchemaTest extends TestCase
{
    private ImageSchema $schema;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->schema = new ImageSchema();
    }

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $this->schema->setName('Test Image');
        $this->schema->setUrl('http://some/image/url.png');
        $this->schema->setHeight(150);
        $this->schema->setWidth(150);

        $this->assertIsArray(
            $this->schema->toArray()
        );
    }
}
