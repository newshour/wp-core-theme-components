<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta\Schemas;

/**
 * Generates schema.org data for SearchAction types.
 */
class SearchActionSchema extends AbstractSchema {

    const SCHEMA_TYPE = 'SearchAction';

    /**
     * @return array
     */
    public function toArray(): array {

        $headers = [
            '@context' => 'https://schema.org',
            '@type' => self::SCHEMA_TYPE
        ];

        return array_merge($headers, parent::toArray());

    }

    /**
     * @return string
     */
    public function getQueryInput(): string {

        return parent::parameters()->get('query-input', '');

    }

    /**
     * @param string $queryInput
     * @return self
     */
    public function setQueryInput($queryInput) {

        if (empty($queryInput)) {

            parent::parameters()->remove('query-input');

        } else {

            parent::parameters()->set('query-input', (string) $queryInput);

        }

        return $this;

    }

    /**
     * @return string
     */
    public function getTarget(): string {

        return parent::parameters()->get('target', '');

    }

    /**
     * @param string $target
     * @return self
     */
    public function setTarget($target): self {

        if (empty($target)) {

            parent::parameters()->remove('target');

        } else {

            parent::parameters()->set('target', (string) $target);

        }

        return $this;

    }

}
