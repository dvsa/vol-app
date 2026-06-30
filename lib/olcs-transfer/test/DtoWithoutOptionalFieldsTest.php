<?php

namespace Dvsa\OlcsTest\Transfer;

use PHPUnit\Framework\Assert as Assert;

/**
 * Trait DtoWithoutOptionalFieldsTest
 */
trait DtoWithoutOptionalFieldsTest
{
    /**
     * @doesNotPerformAssertions
     */
    public function testDefaultValues()
    {
        // the test as defined by DtoTest is only relevant to Dto with optional fields
    }

    /**
     * @inheritDoc
     */
    protected function getOptionalDtoFields()
    {
        return [];
    }
}
