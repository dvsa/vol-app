<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Formatter;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\BrTasNotified;

/**
 * BrTasNotified Test
 */
class BrTasNotifiedTest extends AbstractArrayList
{
    public const SUT_CLASS_NAME = BrTasNotified::class;
    public const ARRAY_FIELD = 'name';
    public const EXPECTED_OUTPUT = '3, abc, 2';
}
