<?php

/**
 * Continuation Controller Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace OlcsTest\Controller\Licence;

use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use Common\Service\Entity\LicenceEntityService as Les;
use Common\Service\Entity\ContinuationDetailEntityService as Cdes;
use OlcsTest\Bootstrap;

/**
 * Continuation Controller Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationControllerTest extends AbstractLvaControllerTestCase
{
    protected $bsm;

    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Licence\ContinuationController');

        $this->bsm = \Mockery::mock();
        $this->sm->setService('BusinessServiceManager', $this->bsm);
    }

    public function testUpdateContinuationActionNotFound()
    {
        $mockContinuationEntityService = \Mockery::mock();
        $this->sm->setService('Entity\ContinuationDetail', $mockContinuationEntityService);

        $entity = [
            'Count' => 3,
            'Results' => []
        ];

        $this->sut->shouldReceive('params->fromRoute')->with('licence', null)->once()->andReturn(22);
        $mockContinuationEntityService->shouldReceive('getContinuationMarker')->with(22)->once()->andReturn($entity);
        $this->sut->shouldReceive('notFoundAction')->with()->once()->andReturn('NOT_FOUND');

        $this->assertEquals('NOT_FOUND', $this->sut->updateContinuationAction());
    }

    public function testUpdateContinuationActionNoPost()
    {
        $mockContinuationEntityService = \Mockery::mock();
        $this->sm->setService('Entity\ContinuationDetail', $mockContinuationEntityService);

        $mockScript = \Mockery::mock();
        $this->sm->setService('Script', $mockScript);

        $entity = [
            'Count' => 1,
            'Results' => [
                ['CONTINUATION_DETAIL'],
            ]
        ];

        $this->sut->shouldReceive('params->fromRoute')->with('licence', null)->once()->andReturn(22);
        $mockContinuationEntityService->shouldReceive('getContinuationMarker')->with(22)->once()->andReturn($entity);
        $this->sut->shouldReceive('getForm')->with('update-continuation')->once()->andReturn('FORM');
        $this->sut->shouldReceive('alterForm')->with('FORM', ['CONTINUATION_DETAIL'])->once();
        $this->sut->shouldReceive('populateFormDefaultValues')->with('FORM', ['CONTINUATION_DETAIL'])->once();
        $this->request->shouldReceive('isPost')->with()->once()->andReturn(false);

        $mockScript->shouldReceive('loadFile')->with('forms/update-continuation')->once();

        $this->sut->shouldReceive('renderView')->once()->andReturn('VIEW');

        $this->assertEquals('VIEW', $this->sut->updateContinuationAction());
    }

    public function testUpdateContinuationActionPrintSeparator()
    {
        $mockContinuationEntityService = \Mockery::mock();
        $this->sm->setService('Entity\ContinuationDetail', $mockContinuationEntityService);

        $entity = [
            'Count' => 1,
            'Results' => [
                [
                    'licence' => [
                        'licNo' => 'L00012'
                    ]
                ],
            ]
        ];

        $this->sut->shouldReceive('params->fromRoute')->with('licence', null)->once()->andReturn(22);
        $mockContinuationEntityService->shouldReceive('getContinuationMarker')->with(22)->once()->andReturn($entity);
        $this->sut->shouldReceive('getForm')->with('update-continuation')->once()->andReturn('FORM');
        $this->sut->shouldReceive('alterForm')->with('FORM', $entity['Results'][0])->once();
        $this->sut->shouldReceive('populateFormDefaultValues')->with('FORM', $entity['Results'][0])->once();
        $this->request->shouldReceive('isPost')->with()->once()->andReturn(true);

        $this->sut->shouldReceive('isButtonPressed')->with('printSeperator')->once()->andReturn(true);

        $mockBsm = \Mockery::mock();
        $this->sm->setService('BusinessServiceManager', $mockBsm);

        $mockCreateSeparatorSheet = \Mockery::mock();
        $mockBsm->shouldReceive('get')->with('CreateSeparatorSheet')->once()->andReturn($mockCreateSeparatorSheet);

        $mockCreateSeparatorSheet->shouldReceive('process')->with(
            [
                'categoryId' => 1,
                'subCategoryId' => 74,
                'entityIdentifier' => 'L00012',
                'descriptionId' => 112,
            ]
        )->once();

        $this->sut->shouldReceive('addSuccessMessage')->with('update-continuation.separator-sheet')->once();
        $this->sut->shouldReceive('redirectToRouteAjax')->with('licence', ['licence' => 22])->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->updateContinuationAction());
    }

    public function testUpdateContinuationActionSubmitFormInValid()
    {
        $mockContinuationEntityService = \Mockery::mock();
        $this->sm->setService('Entity\ContinuationDetail', $mockContinuationEntityService);

        $mockScript = \Mockery::mock();
        $this->sm->setService('Script', $mockScript);

        $mockForm = \Mockery::mock();

        $entity = [
            'Count' => 1,
            'Results' => [
                [
                    'licence' => [
                        'licNo' => 'L00012'
                    ]
                ],
            ]
        ];

        $this->sut->shouldReceive('params->fromRoute')->with('licence', null)->once()->andReturn(22);
        $mockContinuationEntityService->shouldReceive('getContinuationMarker')->with(22)->once()->andReturn($entity);
        $this->sut->shouldReceive('getForm')->with('update-continuation')->once()->andReturn($mockForm);
        $this->sut->shouldReceive('alterForm')->with($mockForm, $entity['Results'][0])->once();
        $this->sut->shouldReceive('populateFormDefaultValues')->with($mockForm, $entity['Results'][0])->once();
        $this->request->shouldReceive('isPost')->with()->once()->andReturn(true);

        $this->sut->shouldReceive('isButtonPressed')->with('printSeperator')->once()->andReturn(false);
        $this->sut->shouldReceive('formPost')->with($mockForm)->once();
        $mockForm->shouldReceive('isValid')->with()->once()->andReturn(false);

        $mockScript->shouldReceive('loadFile')->with('forms/update-continuation')->once();

        $this->sut->shouldReceive('renderView')->once()->andReturn('VIEW');

        $this->assertEquals('VIEW', $this->sut->updateContinuationAction());
    }

    public function testUpdateContinuationActionSave()
    {
        $mockContinuationEntityService = \Mockery::mock();
        $this->sm->setService('Entity\ContinuationDetail', $mockContinuationEntityService);

        $mockScript = \Mockery::mock();
        $this->sm->setService('Script', $mockScript);

        $mockForm = \Mockery::mock();

        $entity = [
            'Count' => 1,
            'Results' => [
                [
                    'licence' => [
                        'licNo' => 'L00012'
                    ]
                ],
            ]
        ];

        $this->sut->shouldReceive('params->fromRoute')->with('licence', null)->once()->andReturn(22);
        $mockContinuationEntityService->shouldReceive('getContinuationMarker')->with(22)->once()->andReturn($entity);
        $this->sut->shouldReceive('getForm')->with('update-continuation')->once()->andReturn($mockForm);
        $this->sut->shouldReceive('alterForm')->with($mockForm, $entity['Results'][0])->once();
        $this->sut->shouldReceive('populateFormDefaultValues')->with($mockForm, $entity['Results'][0])->once();
        $this->request->shouldReceive('isPost')->with()->once()->andReturn(true);
        $this->sut->shouldReceive('isButtonPressed')->with('printSeperator')->once()->andReturn(false);
        $this->sut->shouldReceive('formPost')->with($mockForm)->once();
        $mockForm->shouldReceive('isValid')->with()->once()->andReturn(true);
        $this->sut->shouldReceive('isButtonPressed')->with('submit')->once()->andReturn(true);
        $this->sut->shouldReceive('isButtonPressed')->with('continueLicence')->once()->andReturn(false);
        $mockForm->shouldReceive('getData')->with()->once()->andReturn('FORM_DATA');
        $this->sut->shouldReceive('updateContinuation')->with($entity['Results'][0], 'FORM_DATA')->once();
        $this->sut->shouldReceive('addSuccessMessage')->with('update-continuation.saved')->once();

        $this->sut->shouldReceive('redirectToRouteAjax')->with('licence', ['licence' => 22])->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->updateContinuationAction());
    }

    public function testUpdateContinuationActionContinue()
    {
        $mockContinuationEntityService = \Mockery::mock();
        $this->sm->setService('Entity\ContinuationDetail', $mockContinuationEntityService);

        $mockScript = \Mockery::mock();
        $this->sm->setService('Script', $mockScript);

        $mockContinueLicence = \Mockery::mock();
        $this->bsm->shouldReceive('get')->with('Lva\ContinueLicence')->once()->andReturn($mockContinueLicence);

        $mockForm = \Mockery::mock();

        $entity = [
            'Count' => 1,
            'Results' => [
                [
                    'id' => 76,
                    'licence' => [
                        'licNo' => 'L00012'
                    ]
                ],
            ]
        ];

        $this->sut->shouldReceive('params->fromRoute')->with('licence', null)->once()->andReturn(22);
        $mockContinuationEntityService->shouldReceive('getContinuationMarker')->with(22)->once()->andReturn($entity);
        $this->sut->shouldReceive('getForm')->with('update-continuation')->once()->andReturn($mockForm);
        $this->sut->shouldReceive('alterForm')->with($mockForm, $entity['Results'][0])->once();
        $this->sut->shouldReceive('populateFormDefaultValues')->with($mockForm, $entity['Results'][0])->once();
        $this->request->shouldReceive('isPost')->with()->once()->andReturn(true);
        $this->sut->shouldReceive('isButtonPressed')->with('printSeperator')->once()->andReturn(false);
        $this->sut->shouldReceive('formPost')->with($mockForm)->once();
        $mockForm->shouldReceive('isValid')->with()->once()->andReturn(true);
        $this->sut->shouldReceive('isButtonPressed')->with('submit')->once()->andReturn(false);
        $this->sut->shouldReceive('isButtonPressed')->with('continueLicence')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->with()->once()->andReturn('FORM_DATA');
        $this->sut->shouldReceive('updateContinuation')->with($entity['Results'][0], 'FORM_DATA')->once();
        $mockContinueLicence->shouldReceive('process')->with(['continuationDetailId' => 76])->once();
        $this->sut->shouldReceive('addSuccessMessage')->with('update-continuation.success')->once();

        $this->sut->shouldReceive('redirectToRouteAjax')->with('licence', ['licence' => 22])->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->updateContinuationAction());
    }

    public function testUpdateContinuationMinData()
    {
        $continuationDetail = [
            'id' => 1966,
            'version' => 2015,
        ];
        $formData = [
            'fields' => [
                'received' => 'Y',
            ]
        ];

        $expectedParams = [
            'data' => [
                'id' => $continuationDetail['id'],
                'version' => $continuationDetail['version'],
                'received' => $formData['fields']['received'],
             ]
        ];

        $mockBusinessService = \Mockery::mock();
        $this->bsm->shouldReceive('get')->with('Lva\UpdateContinuationDetail')->once()->andReturn($mockBusinessService);

        $mockBusinessService->shouldReceive('process')->with($expectedParams)->once();

        $this->sut->updateContinuation($continuationDetail, $formData);
    }

    public function testUpdateContinuationAllData()
    {
        $continuationDetail = [
            'id' => 1966,
            'version' => 2015,
        ];
        $formData = [
            'fields' => [
                'received' => 'Y',
                'checklistStatus' => 'A',
                'totalVehicleAuthorisation' => 'B',
                'numberOfDiscs' => 'C',
                'numberOfCommunityLicences' => 'D',
            ]
        ];

        $expectedParams = [
            'data' => [
                'id' => $continuationDetail['id'],
                'version' => $continuationDetail['version'],
                'received' => $formData['fields']['received'],
                'status' => $formData['fields']['checklistStatus'],
                'totAuthVehicles' => $formData['fields']['totalVehicleAuthorisation'],
                'totPsvDiscs' => $formData['fields']['numberOfDiscs'],
                'totCommunityLicences' => $formData['fields']['numberOfCommunityLicences'],
             ]
        ];

        $mockBusinessService = \Mockery::mock();
        $this->bsm->shouldReceive('get')->with('Lva\UpdateContinuationDetail')->once()->andReturn($mockBusinessService);

        $mockBusinessService->shouldReceive('process')->with($expectedParams)->once();

        $this->sut->updateContinuation($continuationDetail, $formData);
    }

    public function testPopulateFormDefaultValues()
    {
        $continuationDetail = [
            'id' => 1966,
            'received' => 'R',
            'status' => [
                'id' => 'STATUS',
            ],
            'licence' => [
            ],
            'totAuthVehicles' => 435,
            'totCommunityLicences' => 765,
            'totPsvDiscs' => 2143,

        ];

        $expectedData = array(
            'fields' => [
                'received' => $continuationDetail['received'],
                'checklistStatus' => $continuationDetail['status']['id'],
                'totalVehicleAuthorisation' => $continuationDetail['totAuthVehicles'],
                'numberOfCommunityLicences' => $continuationDetail['totCommunityLicences'],
                'numberOfDiscs' => $continuationDetail['totPsvDiscs'],
            ]
        );

        $form = \Mockery::mock();
        $form->shouldReceive('populateValues')->with($expectedData)->once();

        $this->sut->populateFormDefaultValues($form, $continuationDetail);
    }

    public function testPopulateFormDefaultValuesFromLicence()
    {
        $continuationDetail = [
            'id' => 1966,
            'received' => 'R',
            'status' => [
                'id' => 'STATUS',
            ],
            'licence' => [
                'id' => 342,
                'totAuthVehicles' => 12,
                'totCommunityLicences' => 453,
                'goodsOrPsv' => Les::LICENCE_CATEGORY_PSV
            ],
            'totAuthVehicles' => null,
            'totCommunityLicences' => null,
            'totPsvDiscs' => null,

        ];

        $expectedData = array(
            'fields' => [
                'received' => $continuationDetail['received'],
                'checklistStatus' => $continuationDetail['status']['id'],
                'totalVehicleAuthorisation' => $continuationDetail['licence']['totAuthVehicles'],
                'numberOfCommunityLicences' => $continuationDetail['licence']['totCommunityLicences'],
                'numberOfDiscs' => 45,
            ]
        );

        $mockEntityService = \Mockery::mock();
        $this->sm->setService('Entity\PsvDisc', $mockEntityService);

        $mockEntityService->shouldReceive('getNotCeasedDiscs')->with(342)->once()->andReturn(['Count' => 45]);

        $mockForm = \Mockery::mock();
        $mockForm->shouldReceive('populateValues')->with($expectedData)->once();

        $this->sut->populateFormDefaultValues($mockForm, $continuationDetail);
    }


    public function testAlterFormWithOutstandingFee()
    {
        $mockEntityService = \Mockery::mock();
        $this->sm->setService('Entity\Fee', $mockEntityService);

        $mockForm = \Mockery::mock();
        $continuationDetail = ['licenceId' => 123, 'licence' => ['LICENCE']];
        $licence = $continuationDetail['licence'];
        $postData = ['POST_DATA'];

        $this->request->shouldReceive('getPost')->with()->once()->andReturn($postData);

        $this->sut->shouldReceive('alterFormReceived')->with($mockForm, $continuationDetail)->once();
        $this->sut->shouldReceive('alterFormChecklistStatus')->with($mockForm, $continuationDetail)->once();
        $this->sut->shouldReceive('alterFormTotalVehicleAuthorisation')->with($mockForm, $licence)->once();
        $this->sut->shouldReceive('alterFormNumberOfDiscs')->with($mockForm, $licence, $postData)->once();
        $this->sut->shouldReceive('alterFormNumberOfCommunityLicences')->with($mockForm, $licence, $postData)->once();

        $mockEntityService->shouldReceive('getOutstandingContinuationFee')->with(123)->once()
            ->andReturn(['Count' => 645]);

        $this->sut->shouldReceive('alterFormActions')->with($mockForm, true, $continuationDetail)->once();

        $mockForm->shouldReceive('get->get->setValue')->once();

        $this->sut->alterForm($mockForm, $continuationDetail);
    }

    public function testAlterFormWithOutOutstandingFee()
    {
        $mockEntityService = \Mockery::mock();
        $this->sm->setService('Entity\Fee', $mockEntityService);

        $mockHelper = \Mockery::mock();
        $this->sm->setService('Helper\Form', $mockHelper);

        $mockForm = \Mockery::mock();
        $continuationDetail = ['licenceId' => 123, 'licence' => ['LICENCE']];
        $licence = $continuationDetail['licence'];
        $postData = ['POST_DATA'];

        $this->request->shouldReceive('getPost')->with()->once()->andReturn($postData);

        $this->sut->shouldReceive('alterFormReceived')->with($mockForm, $continuationDetail)->once();
        $this->sut->shouldReceive('alterFormChecklistStatus')->with($mockForm, $continuationDetail)->once();
        $this->sut->shouldReceive('alterFormTotalVehicleAuthorisation')->with($mockForm, $licence)->once();
        $this->sut->shouldReceive('alterFormNumberOfDiscs')->with($mockForm, $licence, $postData)->once();
        $this->sut->shouldReceive('alterFormNumberOfCommunityLicences')->with($mockForm, $licence, $postData)->once();

        $mockEntityService->shouldReceive('getOutstandingContinuationFee')->with(123)->once()
            ->andReturn(['Count' => 0]);

        $this->sut->shouldReceive('alterFormActions')->with($mockForm, false, $continuationDetail)->once();

        $mockHelper->shouldReceive('remove')->with($mockForm, 'fields->messages')->once();

        $this->sut->alterForm($mockForm, $continuationDetail);
    }

    public function testAlterFormActionsWithFees()
    {
        $mockForm = \Mockery::mock();
        $continuationDetail = [];

        $mockHelper = \Mockery::mock();
        $this->sm->setService('Helper\Form', $mockHelper);

        $mockHelper->shouldReceive('remove')->with($mockForm, 'form-actions->continueLicence')->once();

        $this->sut->alterFormActions($mockForm, true, $continuationDetail);
    }

    public function testAlterFormActionsComplete()
    {
        $mockForm = \Mockery::mock();
        $continuationDetail = [
            'status' => [
                'id' => Cdes::STATUS_COMPLETE
            ]
        ];

        $mockHelper = \Mockery::mock();
        $this->sm->setService('Helper\Form', $mockHelper);

        $mockHelper->shouldReceive('remove')->with($mockForm, 'form-actions->continueLicence')->once();

        $this->sut->alterFormActions($mockForm, false, $continuationDetail);
    }

    public function testAlterFormActionsNotCompleteWithOutFees()
    {
        $mockForm = \Mockery::mock();
        $continuationDetail = [
            'status' => [
                'id' => Cdes::STATUS_PRINTED
            ]
        ];

        $this->sut->alterFormActions($mockForm, false, $continuationDetail);
    }

    public function dataProviderAlterFormReceived()
    {
        return [
            [true, Cdes::STATUS_ACCEPTABLE, 'N'],
            [true, Cdes::STATUS_COMPLETE, 'N'],
            [true, Cdes::STATUS_PREPARED, 'N'],
            [true, Cdes::STATUS_PRINTED, 'N'],
            [true, Cdes::STATUS_PRINTING, 'N'],
            [true, Cdes::STATUS_UNACCEPTABLE, 'N'],
            [false, Cdes::STATUS_ACCEPTABLE, 'Y'],
            [false, Cdes::STATUS_COMPLETE, 'Y'],
            [false, Cdes::STATUS_PREPARED, 'Y'],
            [true, Cdes::STATUS_PRINTED, 'Y'],
            [false, Cdes::STATUS_PRINTING, 'Y'],
            [false, Cdes::STATUS_UNACCEPTABLE, 'Y'],
        ];
    }

    /**
     * @dataProvider dataProviderAlterFormReceived
     *
     * @param bool   $enabled
     * @param string $status
     * @param string $received 'Y' or 'N'
     */
    public function testAlterFormReceived($enabled, $status, $received)
    {
        $mockForm = \Mockery::mock();

        $mockFormHelper = \Mockery::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        if ($enabled) {
            $mockFormHelper->shouldReceive('disableElement')->never();
        } else {
            $mockFormHelper->shouldReceive('disableElement')->with($mockForm, 'fields->received')->once();
        }

        $continuationDetail = ['status' => ['id' => $status], 'received' => $received];
        $this->sut->alterFormReceived($mockForm, $continuationDetail);
    }

    public function dataProviderAlterFormChecklistStatus()
    {
        return [
            [true, Cdes::STATUS_ACCEPTABLE],
            [false, Cdes::STATUS_COMPLETE],
            [false, Cdes::STATUS_PREPARED],
            [true, Cdes::STATUS_PRINTED],
            [false, Cdes::STATUS_PRINTING],
            [true, Cdes::STATUS_UNACCEPTABLE],
        ];
    }

    /**
     * @dataProvider dataProviderAlterFormChecklistStatus
     *
     * @param bool   $enabled
     * @param status $status
     */
    public function testAlterFormChecklistStatus($enabled, $status)
    {
        $mockChecklist = \Mockery::mock();

        $valueOptions = [
            Cdes::STATUS_ACCEPTABLE => 'A',
            Cdes::STATUS_COMPLETE => 'B',
            Cdes::STATUS_ERROR => 'C',
            Cdes::STATUS_PREPARED => 'D',
            Cdes::STATUS_PRINTED => 'E',
            Cdes::STATUS_PRINTING => 'F',
            Cdes::STATUS_UNACCEPTABLE => 'G',
        ];

        $mockForm = \Mockery::mock();

        $mockFormHelper = \Mockery::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $mockForm->shouldReceive('get->get')->twice()->andReturn($mockChecklist);
        $mockChecklist->shouldReceive('getValueOptions')->once()->andReturn($valueOptions);

        if ($enabled) {
            $mockFormHelper->shouldReceive('disableElement')->never();
            $mockChecklist->shouldReceive('setValueOptions')->once()->with(
                [
                    Cdes::STATUS_ACCEPTABLE => 'A',
                    Cdes::STATUS_PRINTED => 'E (not continued)',
                    Cdes::STATUS_UNACCEPTABLE => 'G',
                ]
            )->andReturn();
        } else {
            $mockFormHelper->shouldReceive('disableElement')->with($mockForm, 'fields->checklistStatus')->once();
            $valueOptions[Cdes::STATUS_PRINTED] = 'E (not continued)';
            $mockChecklist->shouldReceive('setValueOptions')->with($valueOptions)->once();
        }

        $continuationDetail = ['status' => ['id' => $status]];
        $this->sut->alterFormChecklistStatus($mockForm, $continuationDetail);
    }

    /**
     * @dataProvider dataProviderFormNumberOfDiscs
     *
     * @param bool   $displayed
     * @param string $goodsOrPsv
     * @param string $licenceType
     */
    public function testAlterFormTotalVehicleAuthorisation($displayed, $goodsOrPsv, $licenceType)
    {
        $mockForm = \Mockery::mock();

        $mockFormHelper = \Mockery::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        if ($displayed) {
            $mockFormHelper->shouldReceive('remove')->never();
        } else {
            $mockFormHelper->shouldReceive('remove')->with($mockForm, 'fields->totalVehicleAuthorisation')->once();
        }

        $licence = ['goodsOrPsv' => $goodsOrPsv, 'licenceType' => $licenceType];
        $this->sut->alterFormTotalVehicleAuthorisation($mockForm, $licence);
    }

    public function dataProviderFormNumberOfDiscs()
    {
        return [
            [false, Les::LICENCE_CATEGORY_GOODS_VEHICLE, Les::LICENCE_TYPE_RESTRICTED],
            [false, Les::LICENCE_CATEGORY_GOODS_VEHICLE, Les::LICENCE_TYPE_SPECIAL_RESTRICTED],
            [false, Les::LICENCE_CATEGORY_GOODS_VEHICLE, Les::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            [false, Les::LICENCE_CATEGORY_GOODS_VEHICLE, Les::LICENCE_TYPE_STANDARD_NATIONAL],
            [true, Les::LICENCE_CATEGORY_PSV, Les::LICENCE_TYPE_RESTRICTED],
            [false, Les::LICENCE_CATEGORY_PSV, Les::LICENCE_TYPE_SPECIAL_RESTRICTED],
            [true, Les::LICENCE_CATEGORY_PSV, Les::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            [true, Les::LICENCE_CATEGORY_PSV, Les::LICENCE_TYPE_STANDARD_NATIONAL],
        ];
    }

    /**
     * @dataProvider dataProviderFormNumberOfDiscs
     *
     * @param bool   $displayed
     * @param string $goodsOrPsv
     * @param string $licenceType
     */
    public function testFormNumberOfDiscs($displayed, $goodsOrPsv, $licenceType)
    {
        $mockForm = \Mockery::mock();

        $mockFormHelper = \Mockery::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        if ($displayed) {
            $mockFormHelper->shouldReceive('remove')->never();

            $this->sm->setService('Translator', \Mockery::mock('Zend\Mvc\I18n\Translator'));

            $mockFormHelper->shouldReceive('attachValidator')->once();
        } else {
            $mockFormHelper->shouldReceive('remove')->with($mockForm, 'fields->numberOfDiscs')->once();
        }

        $licence = ['goodsOrPsv' => $goodsOrPsv, 'licenceType' => $licenceType, 'totAuthVehicles' => 1];
        $this->sut->alterFormNumberOfDiscs($mockForm, $licence, ['fields' => ['totalVehicleAuthorisation' => 50]]);
    }

    public function dataProviderAlterFormNumberOfCommunityLicences()
    {
        return [
            [true, Les::LICENCE_CATEGORY_GOODS_VEHICLE, Les::LICENCE_TYPE_RESTRICTED],
            [true, Les::LICENCE_CATEGORY_GOODS_VEHICLE, Les::LICENCE_TYPE_SPECIAL_RESTRICTED],
            [true, Les::LICENCE_CATEGORY_GOODS_VEHICLE, Les::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            [true, Les::LICENCE_CATEGORY_GOODS_VEHICLE, Les::LICENCE_TYPE_STANDARD_NATIONAL],
            [true, Les::LICENCE_CATEGORY_PSV, Les::LICENCE_TYPE_RESTRICTED],
            [false, Les::LICENCE_CATEGORY_PSV, Les::LICENCE_TYPE_SPECIAL_RESTRICTED],
            [true, Les::LICENCE_CATEGORY_PSV, Les::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            [false, Les::LICENCE_CATEGORY_PSV, Les::LICENCE_TYPE_STANDARD_NATIONAL],
        ];
    }

    /**
     * @dataProvider dataProviderAlterFormNumberOfCommunityLicences
     *
     * @param bool   $displayed
     * @param string $goodsOrPsv
     * @param string $licenceType
     */
    public function testAlterFormNumberOfCommunityLicences($displayed, $goodsOrPsv, $licenceType)
    {
        $mockForm = \Mockery::mock();

        $mockFormHelper = \Mockery::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        if ($displayed) {
            $mockFormHelper->shouldReceive('remove')->never();

            $this->sm->setService('Translator', \Mockery::mock('Zend\Mvc\I18n\Translator'));

            $mockFormHelper->shouldReceive('attachValidator')->once();
        } else {
            $mockFormHelper->shouldReceive('remove')->with($mockForm, 'fields->numberOfCommunityLicences')->once();
        }

        $licence = ['goodsOrPsv' => $goodsOrPsv, 'licenceType' => $licenceType, 'totAuthVehicles' => 4];
        $this->sut->alterFormNumberOfCommunityLicences(
            $mockForm,
            $licence, ['fields' => ['totalVehicleAuthorisation' => 1]]
        );
    }
}
