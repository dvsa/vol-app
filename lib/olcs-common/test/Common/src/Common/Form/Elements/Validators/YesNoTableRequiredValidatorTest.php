<?php

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\YesNoTableRequiredValidator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Test TableRequiredValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class YesNoTableRequiredValidatorTest extends MockeryTestCase
{
    public $validator;
    /**
     * Set up the validator
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->validator = new YesNoTableRequiredValidator([
            'table' => 'testTable',
            'message' => 'testMessage'
        ]);
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
                'Y',
                ['testTable' => ['rows' => 1]],
                true
            ],
            [
                'Y',
                ['testTable' => ['rows' => 0]],
                false
            ],
            [
                'N',
                ['testTable' => ['rows' => 1]],
                true
            ],
            [
                'N',
                ['testTable' => ['rows' => 0]],
                true
            ],
        ];
    }
}
