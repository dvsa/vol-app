<?php

namespace Dvsa\OlcsTest\Transfer\Command\User;

use Dvsa\Olcs\Transfer\Command\User\UpdateUser;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use PHPUnit\Framework\TestCase;

/**
 * Pay Outstanding Fees test
 */
class UpdateUserTest extends TestCase
{
    use CommandTest;

    protected function createBlankDto()
    {
        return new UpdateUser();
    }

    protected function getOptionalDtoFields()
    {
        return [
            'translateToWelsh',
            'team',
            'application',
            'transportManager',
            'localAuthority',
            'partnerContactDetails',
            'accountDisabled',
            'resetPassword',
        ];
    }

    protected function getValidFieldValues()
    {
        return [
            'id' => ['1', '2'],
            'version' => ['1', '2'],
            'userType' => [
                "internal",
                "local-authority",
                "partner",
                "operator",
                "transport-manager"
            ],
            'licenceNumber' => [
                '12',
                str_repeat('a', 18)
            ],
            'roles' => [
                [
                    "system-admin",
                    "internal-limited-read-only",
                    "internal-read-only",
                    "internal-case-worker",
                    "internal-admin",
                    "internal-irhp-admin",
                    "operator-tc",
                    "operator-admin",
                    "operator-user",
                    "operator-tm",
                    "partner-admin",
                    "partner-user",
                    "local-authority-admin",
                    "local-authority-user"
                ]
            ],
            'accountDisabled' => ['Y', 'N'],
            'resetPassword' => ['post', 'email'],
        ];
    }

    protected function getInvalidFieldValues()
    {
        return [
            'id' => ['string', ['unexpected' => 'array']],
            'version' => ['string', ['unexpected' => 'array']],
            'userType' => ["incorrect", ['unexpected' => 'array']],
            'licenceNumber' => ['1', str_repeat('a', 19)],
            'roles' => [["wrong_role"], ['unexpected' => 'array']],
            'accountDisabled' => ['string', ['unexpected' => 'array']],
            'resetPassword' => ['string', ['unexpected' => 'array']],
        ];
    }


    protected function getFilterTransformations()
    {
        return [
            'id' => [[1, '1']],
            'version' => [[2, '2']],
            'resetPassword' => [['email ', 'email']],
            'userType' => [['partner ', 'partner']],
            'application' => [[54, '54']],
            'transportManager' => [[7, '7']],
            'localAuthority' => [[11, '11']],
            'partnerContactDetails' => [[12, '12']],
            'accountDisabled' => [[10, '10']],
        ];
    }
}
