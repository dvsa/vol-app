<?php

/**
 * Test no of permits max validator
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\NoOfPermitsMax;
use Laminas\Validator\LessThan;

/**
 * Test No of permits max validator
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsMaxTest extends \PHPUnit\Framework\TestCase
{
    public $validator;
    /**
     * Set up the validator
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->validator = new NoOfPermitsMax(15);
    }

    /**
     * Test isValid
     *
     * @dataProvider providerIsValid
     */
    public function testIsValid($value, $expected): void
    {
        $this->assertEquals($expected, $this->validator->isValid($value, null));
    }

    public function testMessageTemplates(): void
    {
        $expectedValue = [
            LessThan::NOT_LESS_INCLUSIVE => 'permits.page.no-of-permits.error.max-exceeded'
        ];

        $this->assertEquals(
            $expectedValue,
            $this->validator->getMessageTemplates()
        );
    }

    /**
     * Provider for isValid
     *
     * @return array
     */
    public function providerIsValid()
    {
        return [
            [14, true],
            [15, true],
            [16, false],
            [0, true],
            [7, true],
            [70, false]
        ];
    }
}
