<?php

/**
 * Test VehicleSafetyTachographAnalyserContractorValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\VehicleSafetyTachographAnalyserContractorValidator;

/**
 * Test VehicleSafetyTachographAnalyserContractorValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehicleSafetyTachographAnalyserContractorValidatorTest extends \PHPUnit\Framework\TestCase
{
    public $validator;
    /**
     * Set up the validator
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->validator = new VehicleSafetyTachographAnalyserContractorValidator();
    }

    /**
     * Test isValid
     *
     * @dataProvider providerIsValid
     */
    public function testIsValid($value, $context, $expected): void
    {
        $this->assertEquals($expected, $this->validator->isValid($value, $context));
    }

    /**
     * Provider for isValid
     *
     * @return array
     */
    public function providerIsValid()
    {
        return [
            [
                null,
                ['tachographIns' => '', 'tachographInsName' => ''],
                true
            ],
            [
                null,
                ['tachographIns' => 'tach_internal', 'tachographInsName' => ''],
                true
            ],
            [
                null,
                ['tachographIns' => 'tach_internal', 'tachographInsName' => 'abc'],
                true
            ],
            [
                null,
                ['tachographIns' => 'tach_external', 'tachographInsName' => ''],
                false
            ],
            [
                null,
                ['tachographIns' => 'tach_external', 'tachographInsName' => 'abc'],
                true
            ]
        ];
    }
}
