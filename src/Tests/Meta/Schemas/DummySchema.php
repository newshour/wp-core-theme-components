<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests\Meta\Schemas;

use NewsHour\WPCoreThemeComponents\Components\Meta\Schemas\AbstractSchema;

class DummySchema extends AbstractSchema
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        $headers = [
            '@context' => 'https://schema.org',
            '@id' => '#DummySchema'
        ];

        return array_merge($headers, parent::toArray());
    }
}
