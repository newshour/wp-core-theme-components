<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Meta\Schemas;

use PHPUnit\Framework\TestCase;
use NewsHour\WPCoreThemeComponents\Components\Meta\Schemas\WebPageSchema;
use NewsHour\WPCoreThemeComponents\Tests\DummyCorePost;

class WebPageSchemaTest extends TestCase
{
    private WebPageSchema $schema;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->schema = new WebPageSchema();
    }

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $this->schema->setUrl('http://some-canonical-url.localhost');
        $this->schema->setName('Lorem ipsum');
        $this->schema->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
        $this->schema->setDatePublished(time(), 'UTC');
        $this->schema->setDateModified(time(), 'UTC');

        $this->assertIsArray(
            $this->schema->toArray()
        );
    }

    /**
     * @return void
     */
    public function testCreateFromCorePost(): void
    {
        $created = WebPageSchema::createFromCorePost(new DummyCorePost());
        $this->assertInstanceOf(WebPageSchema::class, $created);
    }
}
