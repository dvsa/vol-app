<?php

namespace Dvsa\OlcsTest\Transfer\Command\Surrender;

use Dvsa\Olcs\Transfer\Command\Surrender\Create;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use Dvsa\OlcsTest\Transfer\DtoWithoutOptionalFieldsTest;

class CreateTest extends \PHPUnit\Framework\TestCase
{
    use CommandTest, DtoWithoutOptionalFieldsTest {
        DtoWithoutOptionalFieldsTest::testDefaultValues insteadof CommandTest;
    }

    protected function createBlankDto()
    {
        return new Create();
    }

    protected function getValidFieldValues()
    {
        return [
            'id' => ['1', '2'],
            'status' => ['surr_sts_start']
        ];
    }

    protected function getInvalidFieldValues()
    {
        return [
            'id' => ['string', ['unexpected' => 'array']],
            'status' => ['string', ['unexpected' => 'array']],
        ];
    }

    protected function getFilterTransformations()
    {
        return [
            'id' => [[99, '99']],
            'status' => [['surr_sts_start ', 'surr_sts_start']],
        ];
    }
}
