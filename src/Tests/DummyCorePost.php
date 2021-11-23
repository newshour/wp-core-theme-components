<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests;

use NewsHour\WPCoreThemeComponents\Models\CorePost;

class DummyCorePost extends CorePost
{
    public function categories(): array
    {
        return [];
    }

    public function excerpt(): string
    {
        return 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
    }

    public function tags(): array
    {
        return [];
    }
}
