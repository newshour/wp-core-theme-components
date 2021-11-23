<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Meta\Schemas;

use PHPUnit\Framework\TestCase;
use NewsHour\WPCoreThemeComponents\Components\Meta\Schemas\SearchActionSchema;

class SearchActionSchemaTest extends TestCase
{
    private SearchActionSchema $schema;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->schema = new SearchActionSchema();
    }

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $this->schema->setQueryInput('required name=search_term');
        $this->schema->setTarget('http://some/search/url.localhost?q={search_term}');

        $this->assertIsArray(
            $this->schema->toArray()
        );
    }
}
