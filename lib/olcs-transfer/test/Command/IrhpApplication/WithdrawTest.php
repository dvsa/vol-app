<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\IrhpApplication\Withdraw;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use Dvsa\OlcsTest\Transfer\DtoWithoutOptionalFieldsTest;
use PHPUnit\Framework\TestCase;

class WithdrawTest extends TestCase
{
    use CommandTest, DtoWithoutOptionalFieldsTest {
        DtoWithoutOptionalFieldsTest::testDefaultValues insteadof CommandTest;
    }

    protected function createBlankDto()
    {
        return new Withdraw();
    }

    protected function getValidFieldValues()
    {
        return [
            'id' => ['1', '2'],
            'reason' => [
                'permits_app_withdraw_by_user',
                'permits_app_withdraw_declined',
                'permits_app_withdraw_not_paid',
            ],
        ];
    }

    protected function getInvalidFieldValues()
    {
        return [
            'id' => ['0', ['array']],
            'reason' => [
                'invalid string',
                ['unexpected' => 'array'],
            ],
        ];
    }

    protected function getFilterTransformations()
    {
        return [
            'id' => [[99, '99'], ['string', '']],
            'reason' => [[' withdraw reason ', 'withdraw reason']],
        ];
    }
}
