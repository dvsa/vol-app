<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\RevocationsSla as Sut;
use Laminas\Form\FormInterface;
use Mockery as m;

class RevocationsSlaTest extends MockeryTestCase
{
    /**
     * testMapFromResult
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromFormDataProvider')]
    public function testMapFromForm(array $input, array $expected): void
    {

        $actual = Sut::mapFromForm($input);
        $this->assertEquals($expected, $actual);
    }

    public static function mapFromFormDataProvider(): array
    {
        return [
            "valid form submission" => [
                [
                    'fields' => [
                        'isSubmissionRequiredForApproval' => '0',
                        'approvalSubmissionIssuedDate' => null,
                        'approvalSubmissionReturnedDate' => null,
                        'approvalSubmissionPresidingTc' => '',
                        'iorLetterIssuedDate' => '2017-11-11',
                        'operatorResponseDueDate' => '2017-11-11',
                        'operatorResponseReceivedDate' => '2017-11-11',
                        'isSubmissionRequiredForAction' => '0',
                        'finalSubmissionIssuedDate' => null,
                        'finalSubmissionReturnedDate' => null,
                        'finalSubmissionPresidingTc' => '',
                        'actionToBeTaken' => 'ptr_action_to_be_taken_revoke',
                        'revocationLetterIssuedDate' => '2017-11-11',
                        'nfaLetterIssuedDate' => null,
                        'warningLetterIssuedDate' => null,
                        'piAgreedDate' => null,
                        'otherActionAgreedDate' => null
                    ],
                    'id' => 1,
                    'version' => 1,
                    'security' => '',
                    'form-actions[continue]' => '',
                    'form-actions' => ['submit' => '', 'cancel' => null]
                ],
                [
                    'isSubmissionRequiredForApproval' => '0',
                    'approvalSubmissionIssuedDate' => null,
                    'approvalSubmissionReturnedDate' => null,
                    'approvalSubmissionPresidingTc' => '',
                    'iorLetterIssuedDate' => '2017-11-11',
                    'operatorResponseDueDate' => '2017-11-11',
                    'operatorResponseReceivedDate' => '2017-11-11',
                    'isSubmissionRequiredForAction' => '0',
                    'finalSubmissionIssuedDate' => null,
                    'finalSubmissionReturnedDate' => null,
                    'finalSubmissionPresidingTc' => '',
                    'actionToBeTaken' => 'ptr_action_to_be_taken_revoke',
                    'revocationLetterIssuedDate' => '2017-11-11',
                    'nfaLetterIssuedDate' => null,
                    'warningLetterIssuedDate' => null,
                    'piAgreedDate' => null,
                    'otherActionAgreedDate' => null,
                    'id' => 1,
                    'version' => 1,
                ]
            ]

        ];
    }

    /**
     * testMapFromResult
     *
     *
     * @param array $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromResultDataProvider')]
    public function testMapFromResult(array $input, mixed $expected): void
    {
        $actual = Sut::mapFromResult($input);
        $this->assertEquals($expected, $actual);
    }

    public static function mapFromResultDataProvider(): array
    {
        return [
            "valid return from backend" => [

                [
                    'isSubmissionRequiredForApproval' => '0',
                    'approvalSubmissionIssuedDate' => null,
                    'approvalSubmissionReturnedDate' => null,
                    'approvalSubmissionPresidingTc' => '',
                    'iorLetterIssuedDate' => '2017-11-11',
                    'operatorResponseDueDate' => '2017-11-11',
                    'operatorResponseReceivedDate' => '2017-11-11',
                    'isSubmissionRequiredForAction' => '0',
                    'finalSubmissionIssuedDate' => null,
                    'finalSubmissionReturnedDate' => null,
                    'finalSubmissionPresidingTc' => '',
                    'actionToBeTaken' => 'ptr_action_to_be_taken_revoke',
                    'revocationLetterIssuedDate' => '2017-11-11',
                    'nfaLetterIssuedDate' => null,
                    'warningLetterIssuedDate' => null,
                    'piAgreedDate' => null,
                    'otherActionAgreedDate' => null,
                    'id' => 1,
                    'version' => 1,
                ],
                [
                    'fields' => [
                        'isSubmissionRequiredForApproval' => '0',
                        'approvalSubmissionIssuedDate' => null,
                        'approvalSubmissionReturnedDate' => null,
                        'approvalSubmissionPresidingTc' => '',
                        'iorLetterIssuedDate' => '2017-11-11',
                        'operatorResponseDueDate' => '2017-11-11',
                        'operatorResponseReceivedDate' => '2017-11-11',
                        'isSubmissionRequiredForAction' => '0',
                        'finalSubmissionIssuedDate' => null,
                        'finalSubmissionReturnedDate' => null,
                        'finalSubmissionPresidingTc' => '',
                        'actionToBeTaken' => 'ptr_action_to_be_taken_revoke',
                        'revocationLetterIssuedDate' => '2017-11-11',
                        'nfaLetterIssuedDate' => null,
                        'warningLetterIssuedDate' => null,
                        'piAgreedDate' => null,
                        'otherActionAgreedDate' => null
                    ],
                    'id' => 1,
                    'version' => 1
                ]
            ]
        ];
    }

    public function testMapFromErrors(): void
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
