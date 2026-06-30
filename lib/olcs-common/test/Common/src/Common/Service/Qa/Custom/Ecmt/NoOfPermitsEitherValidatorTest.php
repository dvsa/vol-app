<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Service\Qa\Custom\Ecmt\NoOfPermitsEitherValidator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsEitherValidatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsEitherValidatorTest extends MockeryTestCase
{
    /**
     * @dataProvider dpIsValid
     */
    public function testIsValid(
        $maxPermitted,
        $value,
        $selectedEmissionsCategory,
        $expectedIsValid,
        $expectedMessages
    ): void {
        $context = [
            'emissionsCategory' => $selectedEmissionsCategory
        ];

        $emissionsCategoryPermitsRemaining = [
            'euro5' => 7,
            'euro6' => 9
        ];

        $noOfPermitsEitherValidator = new NoOfPermitsEitherValidator();
        $noOfPermitsEitherValidator->setOptions(
            [
                'emissionsCategoryPermitsRemaining' => $emissionsCategoryPermitsRemaining,
                'maxPermitted' => $maxPermitted
            ]
        );

        $isValid = $noOfPermitsEitherValidator->isValid($value, $context);

        $this->assertEquals($expectedIsValid, $isValid);
        $this->assertEquals(
            $expectedMessages,
            $noOfPermitsEitherValidator->getMessages()
        );
    }

    /**
     * @return (bool|int|string|string[])[][]
     *
     * @psalm-return array{'no emissions category selected': array{maxPermitted: 4, value: '10', selectedEmissionsCategory: '', expectedIsValid: true, expectedMessages: array<never, never>}, 'euro5 selected, permits remaining less than max permitted, value within bounds': array{maxPermitted: 11, value: '7', selectedEmissionsCategory: 'euro5', expectedIsValid: true, expectedMessages: array<never, never>}, 'euro5 selected, permits remaining less than max permitted, value outside bounds': array{maxPermitted: 11, value: '8', selectedEmissionsCategory: 'euro5', expectedIsValid: false, expectedMessages: array{permitsRemainingThreshold: 'qanda.ecmt.number-of-permits.error.permits-remaining-exceeded.euro5'}}, 'euro5 selected, max permitted less than permits remaining, value within bounds': array{maxPermitted: 5, value: '5', selectedEmissionsCategory: 'euro5', expectedIsValid: true, expectedMessages: array<never, never>}, 'euro5 selected, max permitted less than permits remaining, value outside bounds': array{maxPermitted: 5, value: '6', selectedEmissionsCategory: 'euro5', expectedIsValid: false, expectedMessages: array{maxPermittedThreshold: 'qanda.ecmt.number-of-permits.error.total-max-exceeded'}}, 'euro6 selected, permits remaining less than max permitted, value within bounds': array{maxPermitted: 11, value: '9', selectedEmissionsCategory: 'euro6', expectedIsValid: true, expectedMessages: array<never, never>}, 'euro6 selected, permits remaining less than max permitted, value outside bounds': array{maxPermitted: 11, value: '10', selectedEmissionsCategory: 'euro6', expectedIsValid: false, expectedMessages: array{permitsRemainingThreshold: 'qanda.ecmt.number-of-permits.error.permits-remaining-exceeded.euro6'}}, 'euro6 selected, max permitted less than permits remaining, value within bounds': array{maxPermitted: 5, value: '5', selectedEmissionsCategory: 'euro6', expectedIsValid: true, expectedMessages: array<never, never>}, 'euro6 selected, max permitted less than permits remaining, value outside bounds': array{maxPermitted: 5, value: '6', selectedEmissionsCategory: 'euro6', expectedIsValid: false, expectedMessages: array{maxPermittedThreshold: 'qanda.ecmt.number-of-permits.error.total-max-exceeded'}}}
     */
    public function dpIsValid(): array
    {
        return [
            'no emissions category selected' => [
                'maxPermitted' => 4,
                'value' => '10',
                'selectedEmissionsCategory' => '',
                'expectedIsValid' => true,
                'expectedMessages' => [],
            ],
            'euro5 selected, permits remaining less than max permitted, value within bounds' => [
                'maxPermitted' => 11,
                'value' => '7',
                'selectedEmissionsCategory' => 'euro5',
                'expectedIsValid' => true,
                'expectedMessages' => [],
            ],
            'euro5 selected, permits remaining less than max permitted, value outside bounds' => [
                'maxPermitted' => 11,
                'value' => '8',
                'selectedEmissionsCategory' => 'euro5',
                'expectedIsValid' => false,
                'expectedMessages' => [
                    NoOfPermitsEitherValidator::PERMITS_REMAINING_THRESHOLD =>
                        'qanda.ecmt.number-of-permits.error.permits-remaining-exceeded.euro5'
                ],
            ],
            'euro5 selected, max permitted less than permits remaining, value within bounds' => [
                'maxPermitted' => 5,
                'value' => '5',
                'selectedEmissionsCategory' => 'euro5',
                'expectedIsValid' => true,
                'expectedMessages' => [],
            ],
            'euro5 selected, max permitted less than permits remaining, value outside bounds' => [
                'maxPermitted' => 5,
                'value' => '6',
                'selectedEmissionsCategory' => 'euro5',
                'expectedIsValid' => false,
                'expectedMessages' => [
                    NoOfPermitsEitherValidator::MAX_PERMITTED_THRESHOLD =>
                        'qanda.ecmt.number-of-permits.error.total-max-exceeded'
                ],
            ],
            'euro6 selected, permits remaining less than max permitted, value within bounds' => [
                'maxPermitted' => 11,
                'value' => '9',
                'selectedEmissionsCategory' => 'euro6',
                'expectedIsValid' => true,
                'expectedMessages' => [],
            ],
            'euro6 selected, permits remaining less than max permitted, value outside bounds' => [
                'maxPermitted' => 11,
                'value' => '10',
                'selectedEmissionsCategory' => 'euro6',
                'expectedIsValid' => false,
                'expectedMessages' => [
                    NoOfPermitsEitherValidator::PERMITS_REMAINING_THRESHOLD =>
                        'qanda.ecmt.number-of-permits.error.permits-remaining-exceeded.euro6'
                ],
            ],
            'euro6 selected, max permitted less than permits remaining, value within bounds' => [
                'maxPermitted' => 5,
                'value' => '5',
                'selectedEmissionsCategory' => 'euro6',
                'expectedIsValid' => true,
                'expectedMessages' => [],
            ],
            'euro6 selected, max permitted less than permits remaining, value outside bounds' => [
                'maxPermitted' => 5,
                'value' => '6',
                'selectedEmissionsCategory' => 'euro6',
                'expectedIsValid' => false,
                'expectedMessages' => [
                    NoOfPermitsEitherValidator::MAX_PERMITTED_THRESHOLD =>
                        'qanda.ecmt.number-of-permits.error.total-max-exceeded'
                ],
            ],
        ];
    }
}
