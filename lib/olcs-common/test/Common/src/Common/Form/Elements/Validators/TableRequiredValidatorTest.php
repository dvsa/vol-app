<?php

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\TableRequiredValidator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Test TableRequiredValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TableRequiredValidatorTest extends MockeryTestCase
{
    public $validator;
    /**
     * Set up the validator
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->validator = new TableRequiredValidator();
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
            // With action
            [null, ['action' => 'foo', 'rows' => 0], true],
            [null, ['action' => 'foo', 'rows' => 1], true],
            [null, ['action' => 'foo', 'rows' => 10], true],
            // Without action
            [null, ['rows' => 0], false],
            [null, ['rows' => 1], true],
            [null, ['rows' => 10], true]
        ];
    }

    public function testGetSetRowsRequired(): void
    {
        $validator = new TableRequiredValidator(['rowsRequired' => 2]);

        $this->assertEquals(true, $validator->isValid(null, ['rows' => 2]));
        $this->assertEquals(false, $validator->isValid(null, ['rows' => 1]));
    }
}
