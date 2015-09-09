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

    public function testUpdateContinuationActionNoPost()
    {
        $this->markTestSkipped();

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
        $this->markTestSkipped();

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

        $this->sut->shouldReceive('handleCommand')
            ->with(\Mockery::type(\Dvsa\Olcs\Transfer\Command\Scan\CreateContinuationSeparatorSheet::class))
            ->once()
            ->andReturnUsing(
                function (\Dvsa\Olcs\Transfer\Command\Scan\CreateContinuationSeparatorSheet $command) {
                    $this->assertSame('L00012', $command->getLicNo());

                    return \Mockery::mock()->shouldReceive('isOk')->andReturn(true)->getMock();
                }
            );

        $this->sut->shouldReceive('addSuccessMessage')->with('update-continuation.separator-sheet')->once();
        $this->sut->shouldReceive('redirectToRouteAjax')->with('licence', ['licence' => 22])->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->updateContinuationAction());
    }
    public function testUpdateContinuationActionPrintSeparatorFail()
    {
        $this->markTestSkipped();

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

        $this->sut->shouldReceive('handleCommand')
            ->with(\Mockery::type(\Dvsa\Olcs\Transfer\Command\Scan\CreateContinuationSeparatorSheet::class))
            ->once()
            ->andReturnUsing(
                function (\Dvsa\Olcs\Transfer\Command\Scan\CreateContinuationSeparatorSheet $command) {
                    $this->assertSame('L00012', $command->getLicNo());

                    return \Mockery::mock()->shouldReceive('isOk')->andReturn(false)->getMock();
                }
            );

        $this->sut->shouldReceive('addErrorMessage')->with('unknown-error')->once();
        $this->sut->shouldReceive('redirectToRouteAjax')->with('licence', ['licence' => 22])->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->updateContinuationAction());
    }

    public function testUpdateContinuationActionSubmitFormInValid()
    {
        $this->markTestSkipped();

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
        $this->markTestSkipped();

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
        $this->markTestSkipped();

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
        $this->markTestSkipped();

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
        $this->markTestSkipped();

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
        $this->markTestSkipped();
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

        $this->sut->populateFormDefaultValues($form, $continuationDetail, 34);
    }

    public function testPopulateFormDefaultValuesFromLicence()
    {
        $this->markTestSkipped();
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
                'goodsOrPsv' => ['id' => Les::LICENCE_CATEGORY_PSV]
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

        $mockForm = \Mockery::mock();
        $mockForm->shouldReceive('populateValues')->with($expectedData)->once();

        $this->sut->populateFormDefaultValues($mockForm, $continuationDetail, 45);
    }


    public function testAlterFormWithOutstandingFee()
    {
        $this->markTestSkipped();
        $mockForm = \Mockery::mock();
        $continuationDetail = ['licenceId' => 123, 'licence' => ['LICENCE']];
        $postData = ['POST_DATA'];

        $this->request->shouldReceive('getPost')->with()->once()->andReturn($postData);

        $this->sut->shouldReceive('alterFormReceived')->with($mockForm, $continuationDetail)->once();
        $this->sut->shouldReceive('alterFormChecklistStatus')->with($mockForm, $continuationDetail)->once();
        $this->sut->shouldReceive('alterFormTotalVehicleAuthorisation')->with($mockForm, $continuationDetail)->once();
        $this->sut->shouldReceive('alterFormNumberOfDiscs')->with($mockForm, $continuationDetail, $postData)->once();
        $this->sut->shouldReceive('alterFormNumberOfCommunityLicences')->with($mockForm, $continuationDetail, $postData)
            ->once();

        $this->sut->shouldReceive('alterFormActions')->with($mockForm, true, $continuationDetail)->once();

        $mockForm->shouldReceive('get->get->setValue')->once();

        $this->sut->alterForm($mockForm, $continuationDetail, true);
    }

    public function testAlterFormWithOutOutstandingFee()
    {
        $this->markTestSkipped();
        $mockHelper = \Mockery::mock();
        $this->sm->setService('Helper\Form', $mockHelper);

        $mockForm = \Mockery::mock();
        $continuationDetail = ['licenceId' => 123, 'licence' => ['LICENCE']];
        $postData = ['POST_DATA'];

        $this->request->shouldReceive('getPost')->with()->once()->andReturn($postData);

        $this->sut->shouldReceive('alterFormReceived')->with($mockForm, $continuationDetail)->once();
        $this->sut->shouldReceive('alterFormChecklistStatus')->with($mockForm, $continuationDetail)->once();
        $this->sut->shouldReceive('alterFormTotalVehicleAuthorisation')->with($mockForm, $continuationDetail)->once();
        $this->sut->shouldReceive('alterFormNumberOfDiscs')->with($mockForm, $continuationDetail, $postData)->once();
        $this->sut->shouldReceive('alterFormNumberOfCommunityLicences')->with($mockForm, $continuationDetail, $postData)
            ->once();

        $this->sut->shouldReceive('alterFormActions')->with($mockForm, false, $continuationDetail)->once();

        $mockHelper->shouldReceive('remove')->with($mockForm, 'fields->messages')->once();

        $this->sut->alterForm($mockForm, $continuationDetail, false);
    }

    public function testAlterFormActionsWithFees()
    {
        $this->markTestSkipped();
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
            [true, Cdes::STATUS_ACCEPTABLE, true],
            [false, Cdes::STATUS_COMPLETE, true],
            [false, Cdes::STATUS_PREPARED, true],
            [true, Cdes::STATUS_PRINTED, true],
            [false, Cdes::STATUS_PRINTING, true],
            [true, Cdes::STATUS_UNACCEPTABLE, false],
            [false, Cdes::STATUS_COMPLETE, false],
            [false, Cdes::STATUS_PREPARED, false],
            [true, Cdes::STATUS_PRINTED, false],
            [false, Cdes::STATUS_PRINTING, false],
            [true, Cdes::STATUS_UNACCEPTABLE, false],
        ];
    }

    /**
     * @dataProvider dataProviderAlterFormChecklistStatus
     *
     * @param bool   $enabled
     * @param status $status
     */
    public function testAlterFormChecklistStatus($enabled, $status, $received)
    {
        $mockElement = \Mockery::mock();

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

        $mockForm->shouldReceive('get->get')->andReturn($mockElement);
        $mockElement->shouldReceive('getValueOptions')->once()->andReturn($valueOptions);

        if ($enabled) {
            if (!$received) {
                $mockFormHelper->shouldReceive('disableElement')->once();
            }
            $mockElement->shouldReceive('setValueOptions')->once()->with(
                [
                    Cdes::STATUS_ACCEPTABLE => 'A',
                    Cdes::STATUS_PRINTED => 'E (not continued)',
                    Cdes::STATUS_UNACCEPTABLE => 'G',
                ]
            )->andReturn();
        } else {
            $mockFormHelper->shouldReceive('disableElement')->with($mockForm, 'fields->checklistStatus')->once();
            $valueOptions[Cdes::STATUS_PRINTED] = 'E (not continued)';
            $mockElement->shouldReceive('setValueOptions')->with($valueOptions)->once();
            $mockElement->shouldReceive('setAttribute');
        }

        $continuationDetail = ['status' => ['id' => $status], 'received' => ($received) ? 'Y' : 'N'];
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

        $continuationDetail = [
            'status' => ['id' => 'con_det_sts_acceptable'],
            'licence' => ['goodsOrPsv' => ['id' => $goodsOrPsv], 'licenceType' => ['id' => $licenceType]]
        ];
        $this->sut->alterFormTotalVehicleAuthorisation($mockForm, $continuationDetail);
    }

    /**
     * @dataProvider dataProviderContinuationDetailStatusElementsEnabled
     *
     * @param bool   $enabled
     * @param string $status
     */
    public function testAlterFormTotalVehicleAuthorisationDisables($enabled, $status)
    {
        $mockForm = \Mockery::mock();

        $mockFormHelper = \Mockery::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        if (!$enabled) {
            $mockFormHelper->shouldReceive('disableElement')->with($mockForm, 'fields->totalVehicleAuthorisation')
                ->once();
        }

        $continuationDetail = [
            'status' => ['id' => $status],
            'licence' => ['goodsOrPsv' => ['id' => 'lcat_psv'], 'licenceType' => ['id' => 'ltyp_si']]
        ];
        $this->sut->alterFormTotalVehicleAuthorisation($mockForm, $continuationDetail);
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

        $continuationDetail = [
            'status' => ['id' => 'con_det_sts_acceptable'],
            'licence' => [
                'goodsOrPsv' => ['id' => $goodsOrPsv],
                'licenceType' => ['id' => $licenceType],
                'totAuthVehicles' => 1
            ]
        ];
        $this->sut->alterFormNumberOfDiscs(
            $mockForm,
            $continuationDetail,
            ['fields' => ['totalVehicleAuthorisation' => 50]]
        );
    }

    /**
     * @dataProvider dataProviderContinuationDetailStatusElementsEnabled
     *
     * @param bool   $enabled
     * @param string $status
     */
    public function testFormNumberOfDiscsDisables($enabled, $status)
    {
        $mockForm = \Mockery::mock();

        $mockFormHelper = \Mockery::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        if ($enabled) {
            $this->sm->setService('Translator', \Mockery::mock('Zend\Mvc\I18n\Translator'));
            $mockFormHelper->shouldReceive('attachValidator')->once();
        } else {
            $mockFormHelper->shouldReceive('disableElement')->with($mockForm, 'fields->numberOfDiscs')->once();
        }

        $continuationDetail = [
            'status' => ['id' => $status],
            'licence' => [
                'goodsOrPsv' => ['id' => 'lcat_psv'],
                'licenceType' => ['id' => 'ltyp_si'],
                'totAuthVehicles' => 1
            ]
        ];
        $this->sut->alterFormNumberOfDiscs(
            $mockForm,
            $continuationDetail,
            ['fields' => ['totalVehicleAuthorisation' => 50]]
        );
    }

    public function dataProviderAlterFormNumberOfCommunityLicences()
    {
        return [
            [false, Les::LICENCE_CATEGORY_GOODS_VEHICLE, Les::LICENCE_TYPE_RESTRICTED],
            [false, Les::LICENCE_CATEGORY_GOODS_VEHICLE, Les::LICENCE_TYPE_SPECIAL_RESTRICTED],
            [true, Les::LICENCE_CATEGORY_GOODS_VEHICLE, Les::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            [false, Les::LICENCE_CATEGORY_GOODS_VEHICLE, Les::LICENCE_TYPE_STANDARD_NATIONAL],
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

        $continuationDetail = [
            'status' => ['id' => 'con_det_sts_acceptable'],
            'licence' => [
                'goodsOrPsv' => ['id' => $goodsOrPsv],
                'licenceType' => ['id' => $licenceType],
                'totAuthVehicles' => 4
            ]
        ];
        $this->sut->alterFormNumberOfCommunityLicences(
            $mockForm,
            $continuationDetail,
            ['fields' => ['totalVehicleAuthorisation' => 1]]
        );
    }

    /**
     * @dataProvider dataProviderContinuationDetailStatusElementsEnabled
     *
     * @param bool   $enabled
     * @param string $status
     */
    public function testAlterFormNumberOfCommunityLicencesDisables($enabled, $status)
    {
        $mockForm = \Mockery::mock();

        $mockFormHelper = \Mockery::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $mockFormHelper->shouldReceive('remove')->never();

        $this->sm->setService('Translator', \Mockery::mock('Zend\Mvc\I18n\Translator'));

        if ($enabled) {
            $mockFormHelper->shouldReceive('attachValidator')->once();
        } else {
            $mockFormHelper->shouldReceive('disableElement')->with($mockForm, 'fields->numberOfCommunityLicences')
                ->once();
        }

        $continuationDetail = [
            'status' => ['id' => $status],
            'licence' => [
                'goodsOrPsv' => ['id' => 'lcat_gv'],
                'licenceType' => ['id' => 'ltyp_si'],
                'totAuthVehicles' => 4
            ]
        ];
        $this->sut->alterFormNumberOfCommunityLicences(
            $mockForm,
            $continuationDetail,
            ['fields' => ['totalVehicleAuthorisation' => 1]]
        );
    }

    /**
     * Get Continuation Details status and whether the elements should be enabled
     *
     * @return array
     */
    public function dataProviderContinuationDetailStatusElementsEnabled()
    {
        return [
            [true, Cdes::STATUS_ACCEPTABLE],
            [false, Cdes::STATUS_COMPLETE],
            [false, Cdes::STATUS_ERROR],
            [false, Cdes::STATUS_PREPARED],
            [true, Cdes::STATUS_PRINTED],
            [false, Cdes::STATUS_PRINTING],
            [true, Cdes::STATUS_UNACCEPTABLE],
        ];
    }
}
