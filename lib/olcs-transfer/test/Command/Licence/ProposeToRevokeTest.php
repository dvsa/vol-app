<?php

namespace Dvsa\OlcsTest\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\Command\Licence\ProposeToRevoke;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use Dvsa\OlcsTest\Transfer\DtoWithoutOptionalFieldsTest;
use PHPUnit\Framework\TestCase;

class ProposeToRevokeTest extends TestCase
{
    use CommandTest, DtoWithoutOptionalFieldsTest {
        DtoWithoutOptionalFieldsTest::testDefaultValues insteadof CommandTest;
    }

    /**
     * @inheritDoc
     */
    protected function createBlankDto()
    {
        return new ProposeToRevoke();
    }

    /**
     * @inheritDoc
     */
    protected function getFilterTransformations()
    {
        return [
            'licence' => [[5, '5']],
            'document' => [[5, '5']],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getValidFieldValues()
    {
        return [
            'licence' => ['6'],
            'document' => ['6'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getInvalidFieldValues()
    {
        return [
            'licence' => [[['invalid-array']]],
            'document' => [[['invalid-array']]],
        ];
    }
}
