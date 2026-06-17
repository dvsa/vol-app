<?php

namespace Dvsa\OlcsTest\Transfer;

use PHPUnit\Framework\Assert as Assert;

/**
 * Trait DtoWithoutFieldTransformationsTest
 */
trait DtoWithoutFieldTransformationsTest
{
    /**
     * @doesNotPerformAssertions
     */
    public function testFieldTransformations()
    {
        // the test as defined by DtoTest is only relevant to Dto with filtered fields
    }

    /**
     * @inheritDoc
     */
    protected function getFilterTransformations()
    {
        return [];
    }
}
