<?php

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\RevocationsSla as Sut;
use Laminas\Form\FormInterface;
use Mockery as m;

class RevocationsSlaTest extends MockeryTestCase
{
    /**
     * testMapFromResult
     *
     * @dataProvider mapFromFormDataProvider
     */
    public function testMapFromForm(array $input, array $expected)
    {

        $actual = Sut::mapFromForm($input);
        $this->assertEquals($expected, $actual);
    }

    public function mapFromFormDataProvider()
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
     * @dataProvider mapFromResultDataProvider
     *
     * @param array $expected
     */
    public function testMapFromResult(array $input, $expected)
    {
        $actual = Sut::mapFromResult($input);
        $this->assertEquals($expected, $actual);
    }

    public function mapFromResultDataProvider()
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

    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
