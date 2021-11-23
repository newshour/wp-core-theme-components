<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Meta\Schemas;

use PHPUnit\Framework\TestCase;

use NewsHour\WPCoreThemeComponents\Components\Meta\Schemas\PersonSchema;

class PersonSchemaTest extends TestCase
{
    private PersonSchema $schema;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->schema = new PersonSchema();
    }

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $this->schema->setFirstName('Foo');
        $this->schema->setLastName('Bar');

        $this->assertIsArray(
            $this->schema->toArray()
        );
    }
}
