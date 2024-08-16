<?php

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ApplicationType;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class ApplicationTypeTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator
 */
class ApplicationTypeTest extends TestCase
{
    /**
     * @dataProvider provideIsValid
     * @param $input
     * @param $context
     * @param $valid
     * @param string $error
     */
    public function testIsValid($input, $context, $valid, $error = '')
    {
        $sut = new ApplicationType();
        $this->assertEquals($valid, $sut->isValid($input, $context));

        if ($error != '') {
            $this->assertEquals($error, key($sut->getMessages()));
        }
    }

    /**
     * Data provider for testIsValid
     *
     * @return array
     */
    public function provideIsValid()
    {
        return [
            [
                ['txcAppType' => BusRegEntity::TXC_APP_NEW],
                ['submissionType' => EbsrSubmission::DATA_REFRESH_SUBMISSION_TYPE],
                false,
                ApplicationType::REFRESH_SUBMISSION_ERROR
            ],
            [
                ['txcAppType' => BusRegEntity::TXC_APP_NEW],
                ['submissionType' => EbsrSubmission::NEW_SUBMISSION_TYPE],
                true
            ],
            [
                ['txcAppType' => BusRegEntity::TXC_APP_NON_CHARGEABLE],
                ['submissionType' => EbsrSubmission::DATA_REFRESH_SUBMISSION_TYPE],
                true
            ],
            [
                ['txcAppType' => BusRegEntity::TXC_APP_NON_CHARGEABLE],
                ['submissionType' => EbsrSubmission::NEW_SUBMISSION_TYPE],
                false,
                ApplicationType::NEW_SUBMISSION_ERROR
            ],
            [
                ['txcAppType' => BusRegEntity::TXC_APP_CHARGEABLE],
                ['submissionType' => EbsrSubmission::DATA_REFRESH_SUBMISSION_TYPE],
                false,
                ApplicationType::REFRESH_SUBMISSION_ERROR
            ],
            [
                ['txcAppType' => BusRegEntity::TXC_APP_CHARGEABLE],
                ['submissionType' => EbsrSubmission::NEW_SUBMISSION_TYPE],
                true
            ],
            [
                ['txcAppType' => BusRegEntity::TXC_APP_CANCEL],
                ['submissionType' => EbsrSubmission::DATA_REFRESH_SUBMISSION_TYPE],
                false,
                ApplicationType::REFRESH_SUBMISSION_ERROR
            ],
            [
                ['txcAppType' => BusRegEntity::TXC_APP_CANCEL],
                ['submissionType' => EbsrSubmission::NEW_SUBMISSION_TYPE],
                true
            ]
        ];
    }
}
