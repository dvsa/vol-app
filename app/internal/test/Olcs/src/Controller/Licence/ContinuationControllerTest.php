<?php

/**
 * Continuation Controller Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace OlcsTest\Controller\Licence;

use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\Form\Form;
use Laminas\Validator\LessThan;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Licence\ContinuationController;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use Mockery as m;

/**
 * Continuation Controller Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->mockScriptFactory = m::mock(ScriptFactory::class);
        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->mockTableFactory = m::mock(TableFactory::class);
        $this->mockViewHelperManager = m::mock(HelperPluginManager::class);
        $this->mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $this->mockLessThanValidator = m::mock(LessThan::class);
        $this->mockController(ContinuationController::class, [
            $this->mockScriptFactory,
            $this->mockFormHelper,
            $this->mockTableFactory,
            $this->mockViewHelperManager,
            $this->mockFlashMessengerHelper,
            $this->mockLessThanValidator,
        ]);
    }

    public function testAlterFormActionsComplete()
    {
        $mockForm = \Mockery::mock(Form::class);
        $continuationDetail = [
            'status' => [
                'id' => RefData::CONTINUATION_DETAIL_STATUS_COMPLETE
            ]
        ];

        $this->mockFormHelper->shouldReceive('remove')->with($mockForm, 'form-actions->continueLicence')->once();

        $this->sut->alterFormActions($mockForm, false, $continuationDetail);
    }

    public function testAlterFormActionsNotCompleteWithOutFees()
    {
        $mockForm = \Mockery::mock(Form::class);
        $continuationDetail = [
            'status' => [
                'id' => RefData::CONTINUATION_DETAIL_STATUS_PRINTED
            ]
        ];

        $this->sut->alterFormActions($mockForm, false, $continuationDetail);
    }

    public function dataProviderAlterFormReceived()
    {
        return [
            [true, RefData::CONTINUATION_DETAIL_STATUS_ACCEPTABLE, 'N'],
            [true, RefData::CONTINUATION_DETAIL_STATUS_COMPLETE, 'N'],
            [true, RefData::CONTINUATION_DETAIL_STATUS_PREPARED, 'N'],
            [true, RefData::CONTINUATION_DETAIL_STATUS_PRINTED, 'N'],
            [true, RefData::CONTINUATION_DETAIL_STATUS_PRINTING, 'N'],
            [true, RefData::CONTINUATION_DETAIL_STATUS_UNACCEPTABLE, 'N'],
            [false, RefData::CONTINUATION_DETAIL_STATUS_ACCEPTABLE, 'Y'],
            [false, RefData::CONTINUATION_DETAIL_STATUS_COMPLETE, 'Y'],
            [false, RefData::CONTINUATION_DETAIL_STATUS_PREPARED, 'Y'],
            [true, RefData::CONTINUATION_DETAIL_STATUS_PRINTED, 'Y'],
            [false, RefData::CONTINUATION_DETAIL_STATUS_PRINTING, 'Y'],
            [false, RefData::CONTINUATION_DETAIL_STATUS_UNACCEPTABLE, 'Y'],
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
        $mockForm = \Mockery::mock(Form::class);

        if ($enabled) {
            $this->mockFormHelper->shouldReceive('disableElement')->never();
        } else {
            $this->mockFormHelper->shouldReceive('disableElement')->with($mockForm, 'fields->received')->once();
        }

        $continuationDetail = ['status' => ['id' => $status], 'received' => $received];
        $this->sut->alterFormReceived($mockForm, $continuationDetail);
    }

    public function dataProviderAlterFormChecklistStatus()
    {
        return [
            [true, RefData::CONTINUATION_DETAIL_STATUS_ACCEPTABLE, true],
            [false, RefData::CONTINUATION_DETAIL_STATUS_COMPLETE, true],
            [false, RefData::CONTINUATION_DETAIL_STATUS_PREPARED, true],
            [true, RefData::CONTINUATION_DETAIL_STATUS_PRINTED, true],
            [false, RefData::CONTINUATION_DETAIL_STATUS_PRINTING, true],
            [true, RefData::CONTINUATION_DETAIL_STATUS_UNACCEPTABLE, false],
            [false, RefData::CONTINUATION_DETAIL_STATUS_COMPLETE, false],
            [false, RefData::CONTINUATION_DETAIL_STATUS_PREPARED, false],
            [true, RefData::CONTINUATION_DETAIL_STATUS_PRINTED, false],
            [false, RefData::CONTINUATION_DETAIL_STATUS_PRINTING, false],
            [true, RefData::CONTINUATION_DETAIL_STATUS_UNACCEPTABLE, false],
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
            RefData::CONTINUATION_DETAIL_STATUS_ACCEPTABLE => 'A',
            RefData::CONTINUATION_DETAIL_STATUS_COMPLETE => 'B',
            RefData::CONTINUATION_DETAIL_STATUS_ERROR => 'C',
            RefData::CONTINUATION_DETAIL_STATUS_PREPARED => 'D',
            RefData::CONTINUATION_DETAIL_STATUS_PRINTED => 'E',
            RefData::CONTINUATION_DETAIL_STATUS_PRINTING => 'F',
            RefData::CONTINUATION_DETAIL_STATUS_UNACCEPTABLE => 'G',
        ];

        $mockForm = \Mockery::mock(Form::class);

        $mockForm->shouldReceive('get->get')->andReturn($mockElement);
        $mockElement->shouldReceive('getValueOptions')->once()->andReturn($valueOptions);

        if ($enabled) {
            if (!$received) {
                $this->mockFormHelper->shouldReceive('disableElement')->once();
            }
            $mockElement->shouldReceive('setValueOptions')->once()->with(
                [
                    RefData::CONTINUATION_DETAIL_STATUS_ACCEPTABLE => 'A',
                    RefData::CONTINUATION_DETAIL_STATUS_PRINTED => 'E (not continued)',
                    RefData::CONTINUATION_DETAIL_STATUS_UNACCEPTABLE => 'G',
                ]
            )->andReturn();
        } else {
            $this->mockFormHelper->shouldReceive('disableElement')->with($mockForm, 'fields->checklistStatus')->once();
            $valueOptions[RefData::CONTINUATION_DETAIL_STATUS_PRINTED] = 'E (not continued)';
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
        $mockForm = \Mockery::mock(Form::class);

        if ($displayed) {
            $this->mockFormHelper->shouldReceive('remove')->never();
        } else {
            $this->mockFormHelper->shouldReceive('remove')->with($mockForm, 'fields->totalVehicleAuthorisation')->once();
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
        $mockForm = \Mockery::mock(Form::class);

        if (!$enabled) {
            $this->mockFormHelper->shouldReceive('disableElement')->with($mockForm, 'fields->totalVehicleAuthorisation')
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
            [false, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_RESTRICTED],
            [false, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_SPECIAL_RESTRICTED],
            [false, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            [false, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_STANDARD_NATIONAL],
            [true, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_RESTRICTED],
            [false, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_SPECIAL_RESTRICTED],
            [true, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            [true, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_STANDARD_NATIONAL],
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
        $mockForm = \Mockery::mock(Form::class);
        $formField = 'fields->numberOfDiscs';
        $totalVehicleAuth = 50;

        if ($displayed) {
            $this->mockFormHelper->shouldReceive('remove')->never();
            $this->mockLessThanValidator->expects('setMax')->with($totalVehicleAuth);
            $this->mockFormHelper->expects('attachValidator')->with(
                $mockForm,
                $formField,
                $this->mockLessThanValidator
            );
        } else {
            $this->mockFormHelper->expects('remove')->with($mockForm, $formField);
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
            ['fields' => ['totalVehicleAuthorisation' => $totalVehicleAuth]],
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
        $mockForm = \Mockery::mock(Form::class);
        $formField = 'fields->numberOfDiscs';
        $totalVehicleAuth = 50;

        if ($enabled) {
            $this->mockLessThanValidator->expects('setMax')->with($totalVehicleAuth);
            $this->mockFormHelper->expects('attachValidator')->with(
                $mockForm,
                $formField,
                $this->mockLessThanValidator
            );
        } else {
            $this->mockFormHelper->expects('disableElement')->with($mockForm, $formField);
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
            ['fields' => ['totalVehicleAuthorisation' => $totalVehicleAuth]],
        );
    }

    public function dataProviderAlterFormNumberOfCommunityLicences(): array
    {
        return [
            [false, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_RESTRICTED, 4],
            [false, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_SPECIAL_RESTRICTED, 4],
            [true, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL, 4],
            [false, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_STANDARD_NATIONAL, 4],
            [true, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_RESTRICTED, 1],
            [false, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_SPECIAL_RESTRICTED, 1],
            [true, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL, 1],
            [false, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_STANDARD_NATIONAL, 1],
        ];
    }

    /**
     * @dataProvider dataProviderAlterFormNumberOfCommunityLicences
     *
     * @param bool   $displayed
     * @param string $goodsOrPsv
     * @param string $licenceType
     */
    public function testAlterFormNumberOfCommunityLicences($displayed, $goodsOrPsv, $licenceType, $maxAuth)
    {
        $mockForm = \Mockery::mock(Form::class);
        $formField = 'fields->numberOfCommunityLicences';

        if ($displayed) {
            $this->mockFormHelper->shouldReceive('remove')->never();
            $this->mockLessThanValidator->expects('setMax')->with($maxAuth);
            $this->mockFormHelper->expects('attachValidator')->with(
                $mockForm,
                $formField,
                $this->mockLessThanValidator
            );
        } else {
            $this->mockFormHelper->expects('remove')->with($mockForm, $formField);
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
        $mockForm = \Mockery::mock(Form::class);
        $totalVehicleAuth = 4;
        $formField = 'fields->numberOfCommunityLicences';

        $this->mockFormHelper->shouldReceive('remove')->never();

        if ($enabled) {
            $this->mockLessThanValidator->expects('setMax')->with($totalVehicleAuth);
            $this->mockFormHelper->expects('attachValidator')->with(
                $mockForm,
                $formField,
                $this->mockLessThanValidator
            );
        } else {
            $this->mockFormHelper->expects('disableElement')->with($mockForm, $formField);
        }

        $continuationDetail = [
            'status' => ['id' => $status],
            'licence' => [
                'goodsOrPsv' => ['id' => 'lcat_gv'],
                'licenceType' => ['id' => 'ltyp_si'],
                'totAuthVehicles' => $totalVehicleAuth,
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
            [true, RefData::CONTINUATION_DETAIL_STATUS_ACCEPTABLE],
            [false, RefData::CONTINUATION_DETAIL_STATUS_COMPLETE],
            [false, RefData::CONTINUATION_DETAIL_STATUS_ERROR],
            [false, RefData::CONTINUATION_DETAIL_STATUS_PREPARED],
            [true, RefData::CONTINUATION_DETAIL_STATUS_PRINTED],
            [false, RefData::CONTINUATION_DETAIL_STATUS_PRINTING],
            [true, RefData::CONTINUATION_DETAIL_STATUS_UNACCEPTABLE],
        ];
    }
}
