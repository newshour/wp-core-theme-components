<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Meta\Schemas;

use PHPUnit\Framework\TestCase;
use NewsHour\WPCoreThemeComponents\Components\Meta\Schemas\NewsArticleSchema;

class NewsArticleSchemaTest extends TestCase
{
    private NewsArticleSchema $schema;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->schema = new NewsArticleSchema();
    }

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $this->schema->setUrl('http://some-canonical-url.localhost');
        $this->schema->setHeadline('Lorem ipsum');
        $this->schema->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
        $this->schema->setDatePublished(time(), 'UTC');
        $this->schema->setDateModified(time(), 'UTC');

        $this->assertIsArray(
            $this->schema->toArray()
        );
    }
}
