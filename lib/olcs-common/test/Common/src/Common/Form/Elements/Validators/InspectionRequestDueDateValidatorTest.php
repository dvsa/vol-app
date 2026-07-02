<?php

/**
 * Test InspectionRequestDueDate
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\InspectionRequestDueDate;

/**
 * Test InspectionRequestDueDate
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InspectionRequestDueDateValidatorTest extends \PHPUnit\Framework\TestCase
{
    public $validator;
    /**
     * Set up the validator
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->validator = new InspectionRequestDueDate();
    }

    /**
     * Test isValid
     *
     * @group inspectionRequestDueDateValidator
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
                '2015-01-01',
                [
                    'requestDate' => [
                        'year' => '2014',
                        'month' => '01',
                        'day' => '01'
                    ]
                ],
                true
            ],
            [
                '2015-02-01',
                [
                    'requestDate' => [
                        'year' => '2015',
                        'month' => '01',
                        'day' => '01'
                    ]
                ],
                true
            ],
            [
                '2014-01-01',
                [
                    'requestDate' => [
                        'year' => '2015',
                        'month' => '01',
                        'day' => '01'
                    ]
                ],
                false
            ],
        ];
    }
}
