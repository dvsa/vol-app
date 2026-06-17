<?php

namespace Dvsa\OlcsTest\Transfer;

use PHPUnit\Framework\Assert as Assert;

/**
 * Trait DtoWithoutInvalidFieldTest
 */
trait DtoWithoutInvalidFieldTest
{
    /**
     * @doesNotPerformAssertions
     */
    public function testInvalidField()
    {
        // the test as defined by DtoTest is only relevant to Dto with fields validation
    }

    /**
     * @inheritDoc
     */
    protected function getInvalidFieldValues()
    {
        return [];
    }
}
