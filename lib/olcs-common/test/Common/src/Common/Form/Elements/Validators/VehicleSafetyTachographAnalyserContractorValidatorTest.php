<?php

/**
 * Test VehicleSafetyTachographAnalyserContractorValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\VehicleSafetyTachographAnalyserContractorValidator;

/**
 * Test VehicleSafetyTachographAnalyserContractorValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class VehicleSafetyTachographAnalyserContractorValidatorTest extends \PHPUnit\Framework\TestCase
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
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerIsValid')]
    public function testIsValid($value, $context, $expected): void
    {
        $this->assertEquals($expected, $this->validator->isValid($value, $context));
    }

    /**
     * Provider for isValid
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function providerIsValid(): \Iterator
    {
        yield [
            null,
            ['tachographIns' => '', 'tachographInsName' => ''],
            true
        ];
        yield [
            null,
            ['tachographIns' => 'tach_internal', 'tachographInsName' => ''],
            true
        ];
        yield [
            null,
            ['tachographIns' => 'tach_internal', 'tachographInsName' => 'abc'],
            true
        ];
        yield [
            null,
            ['tachographIns' => 'tach_external', 'tachographInsName' => ''],
            false
        ];
        yield [
            null,
            ['tachographIns' => 'tach_external', 'tachographInsName' => 'abc'],
            true
        ];
    }
}
