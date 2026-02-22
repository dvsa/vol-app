<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Formatter;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\BrServiceTypes;

/**
 * BrServiceTypes Test
 */
class BrServiceTypesTest extends AbstractArrayList
{
    public const SUT_CLASS_NAME = BrServiceTypes::class;
    public const ARRAY_FIELD = 'description';
    public const EXPECTED_OUTPUT = '3, abc, 2';
}
