<?php

namespace Dvsa\OlcsTest\Transfer\Command\User;

use Dvsa\Olcs\Transfer\Command\User\UpdateUserLastLoginAt;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use Dvsa\OlcsTest\Transfer\DtoWithoutInvalidFieldTest;
use Dvsa\OlcsTest\Transfer\DtoWithoutOptionalFieldsTest;
use PHPUnit\Framework\TestCase;

/**
 * Update User Last Login At test
 */
class UpdateUserLastLoginAtTest extends TestCase
{
    use CommandTest, DtoWithoutInvalidFieldTest, DtoWithoutOptionalFieldsTest {
        DtoWithoutInvalidFieldTest::testInvalidField insteadof CommandTest;
        DtoWithoutOptionalFieldsTest::testDefaultValues insteadof CommandTest;
    }

    protected function createBlankDto()
    {
        return new UpdateUserLastLoginAt();
    }

    protected function getValidFieldValues()
    {
        return [
            'secureToken' => [
                'AQIC5wM2LY4SfczoZQTpr-5U3AuSvh7z3PWQQVMBF7ws3t0.%2AAAJTSQACMDIAAlNLABM4Nzk1ODA5NTg5MjA2NDMwNjk2AAJTMQACMDE.%2A',
                'AQIC5wM2LY4Sfcx-A3rZVxSIGZoaqwJjloBVJEHl2ThcxOc.%2AAAJTSQACMDIAAlNLABQtNDU1NzIzMzk3MDI5NTM1MjkwNAACUzEAAjAx%2A'
            ]
        ];
    }

    protected function getFilterTransformations()
    {
        return [
            'secureToken' => [
                [
                    '   AQIC5wM2LY4SfczoZQTpr-5U3AuSvh7z3PWQQVMBF7ws3t0.%2AAAJTSQACMDIAAlNLABM4Nzk1ODA5NTg5MjA2NDMwNjk2AAJTMQACMDE.%2A',
                    'AQIC5wM2LY4SfczoZQTpr-5U3AuSvh7z3PWQQVMBF7ws3t0.%2AAAJTSQACMDIAAlNLABM4Nzk1ODA5NTg5MjA2NDMwNjk2AAJTMQACMDE.%2A'
                ],
                [
                    'AQIC5wM2LY4Sfcx-A3rZVxSIGZoaqwJjloBVJEHl2ThcxOc.%2AAAJTSQACMDIAAlNLABQtNDU1NzIzMzk3MDI5NTM1MjkwNAACUzEAAjAx%2A    ',
                    'AQIC5wM2LY4Sfcx-A3rZVxSIGZoaqwJjloBVJEHl2ThcxOc.%2AAAJTSQACMDIAAlNLABQtNDU1NzIzMzk3MDI5NTM1MjkwNAACUzEAAjAx%2A',
                ]
            ]
        ];
    }
}
