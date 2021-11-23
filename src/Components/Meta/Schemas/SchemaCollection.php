<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta\Schemas;

use InvalidArgumentException;
use SplObjectStorage;

/**
 * A collection of Schema objects.
 */
class SchemaCollection extends SplObjectStorage
{
    /**
     * Attach a Schema to the collection.
     *
     * @param Schema $object
     * @param mixed $info
     * @throws InvalidArgumentException
     * @return void
     */
    public function attach($object, $info = null)
    {
        if (!($object instanceof Schema)) {
            throw new InvalidArgumentException("Object is not of type Schema.");
        }

        parent::attach($object, $info);
    }

    /**
     * Return the Schema collection as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $final = [];

        if (parent::count() > 0) {
            parent::rewind();

            while (parent::valid()) {
                $object = parent::current();
                $final[] = $object->toArray();
                parent::next();
            }
        }

        return $final;
    }
}
