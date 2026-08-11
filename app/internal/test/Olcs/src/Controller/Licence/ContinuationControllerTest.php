<?php

declare(strict_types=1);

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
final class ContinuationControllerTest extends AbstractLvaControllerTestCase
{
    protected $sut;
    protected $mockFormHelper;
    protected $mockLessThanValidator;

    public function setUp(): void
    {
        parent::setUp();

        $mockScriptFactory = m::mock(ScriptFactory::class);
        $this->mockFormHelper = m::mock(FormHelperService::class);
        $mockTableFactory = m::mock(TableFactory::class);
        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $this->mockLessThanValidator = m::mock(LessThan::class);
        $this->mockController(ContinuationController::class, [
            $mockScriptFactory,
            $this->mockFormHelper,
            $mockTableFactory,
            $mockViewHelperManager,
            $mockFlashMessengerHelper,
            $this->mockLessThanValidator,
        ]);
    }

    public function testAlterFormActionsComplete(): void
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

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testAlterFormActionsNotCompleteWithOutFees(): void
    {
        $mockForm = \Mockery::mock(Form::class);
        $continuationDetail = [
            'status' => [
                'id' => RefData::CONTINUATION_DETAIL_STATUS_PRINTED
            ]
        ];

        $this->sut->alterFormActions($mockForm, false, $continuationDetail);
    }

    public static function dataProviderAlterFormReceived(): \Iterator
    {
        yield [true, RefData::CONTINUATION_DETAIL_STATUS_ACCEPTABLE, 'N'];
        yield [true, RefData::CONTINUATION_DETAIL_STATUS_COMPLETE, 'N'];
        yield [true, RefData::CONTINUATION_DETAIL_STATUS_PREPARED, 'N'];
        yield [true, RefData::CONTINUATION_DETAIL_STATUS_PRINTED, 'N'];
        yield [true, RefData::CONTINUATION_DETAIL_STATUS_PRINTING, 'N'];
        yield [true, RefData::CONTINUATION_DETAIL_STATUS_UNACCEPTABLE, 'N'];
        yield [false, RefData::CONTINUATION_DETAIL_STATUS_ACCEPTABLE, 'Y'];
        yield [false, RefData::CONTINUATION_DETAIL_STATUS_COMPLETE, 'Y'];
        yield [false, RefData::CONTINUATION_DETAIL_STATUS_PREPARED, 'Y'];
        yield [true, RefData::CONTINUATION_DETAIL_STATUS_PRINTED, 'Y'];
        yield [false, RefData::CONTINUATION_DETAIL_STATUS_PRINTING, 'Y'];
        yield [false, RefData::CONTINUATION_DETAIL_STATUS_UNACCEPTABLE, 'Y'];
    }

    /**
     *
     * @param bool   $enabled
     * @param string $status
     * @param string $received 'Y' or 'N'
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderAlterFormReceived')]
    public function testAlterFormReceived(mixed $enabled, mixed $status, mixed $received): void
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

    public static function dataProviderAlterFormChecklistStatus(): \Iterator
    {
        yield [true, RefData::CONTINUATION_DETAIL_STATUS_ACCEPTABLE, true];
        yield [false, RefData::CONTINUATION_DETAIL_STATUS_COMPLETE, true];
        yield [false, RefData::CONTINUATION_DETAIL_STATUS_PREPARED, true];
        yield [true, RefData::CONTINUATION_DETAIL_STATUS_PRINTED, true];
        yield [false, RefData::CONTINUATION_DETAIL_STATUS_PRINTING, true];
        yield [true, RefData::CONTINUATION_DETAIL_STATUS_UNACCEPTABLE, false];
        yield [false, RefData::CONTINUATION_DETAIL_STATUS_COMPLETE, false];
        yield [false, RefData::CONTINUATION_DETAIL_STATUS_PREPARED, false];
        yield [true, RefData::CONTINUATION_DETAIL_STATUS_PRINTED, false];
        yield [false, RefData::CONTINUATION_DETAIL_STATUS_PRINTING, false];
        yield [true, RefData::CONTINUATION_DETAIL_STATUS_UNACCEPTABLE, false];
    }

    /**
     *
     * @param bool   $enabled
     * @param status $status
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderAlterFormChecklistStatus')]
    public function testAlterFormChecklistStatus(mixed $enabled, mixed $status, mixed $received): void
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
     *
     * @param bool   $displayed
     * @param string $goodsOrPsv
     * @param string $licenceType
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderFormNumberOfDiscs')]
    public function testAlterFormTotalVehicleAuthorisation(mixed $displayed, mixed $goodsOrPsv, mixed $licenceType): void
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
     *
     * @param bool   $enabled
     * @param string $status
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderContinuationDetailStatusElementsEnabled')]
    public function testAlterFormTotalVehicleAuthorisationDisables(mixed $enabled, mixed $status): void
    {
        if ($enabled) {
            $this->expectNotToPerformAssertions();
        }

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

    public static function dataProviderFormNumberOfDiscs(): \Iterator
    {
        yield [false, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_RESTRICTED];
        yield [false, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_SPECIAL_RESTRICTED];
        yield [false, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL];
        yield [false, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_STANDARD_NATIONAL];
        yield [true, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_RESTRICTED];
        yield [false, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_SPECIAL_RESTRICTED];
        yield [true, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL];
        yield [true, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_STANDARD_NATIONAL];
    }

    /**
     *
     * @param bool   $displayed
     * @param string $goodsOrPsv
     * @param string $licenceType
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderFormNumberOfDiscs')]
    public function testFormNumberOfDiscs(mixed $displayed, mixed $goodsOrPsv, mixed $licenceType): void
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
     *
     * @param bool   $enabled
     * @param string $status
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderContinuationDetailStatusElementsEnabled')]
    public function testFormNumberOfDiscsDisables(mixed $enabled, mixed $status): void
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

    public static function dataProviderAlterFormNumberOfCommunityLicences(): \Iterator
    {
        yield [false, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_RESTRICTED, 4];
        yield [false, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_SPECIAL_RESTRICTED, 4];
        yield [true, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL, 4];
        yield [false, RefData::LICENCE_CATEGORY_GOODS_VEHICLE, RefData::LICENCE_TYPE_STANDARD_NATIONAL, 4];
        yield [true, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_RESTRICTED, 1];
        yield [false, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_SPECIAL_RESTRICTED, 1];
        yield [true, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL, 1];
        yield [false, RefData::LICENCE_CATEGORY_PSV, RefData::LICENCE_TYPE_STANDARD_NATIONAL, 1];
    }

    /**
     *
     * @param bool   $displayed
     * @param string $goodsOrPsv
     * @param string $licenceType
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderAlterFormNumberOfCommunityLicences')]
    public function testAlterFormNumberOfCommunityLicences(mixed $displayed, mixed $goodsOrPsv, mixed $licenceType, mixed $maxAuth): void
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
     *
     * @param bool   $enabled
     * @param string $status
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderContinuationDetailStatusElementsEnabled')]
    public function testAlterFormNumberOfCommunityLicencesDisables(mixed $enabled, mixed $status): void
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
     * @return \Iterator<(int | string), mixed>
     */
    public static function dataProviderContinuationDetailStatusElementsEnabled(): \Iterator
    {
        yield [true, RefData::CONTINUATION_DETAIL_STATUS_ACCEPTABLE];
        yield [false, RefData::CONTINUATION_DETAIL_STATUS_COMPLETE];
        yield [false, RefData::CONTINUATION_DETAIL_STATUS_ERROR];
        yield [false, RefData::CONTINUATION_DETAIL_STATUS_PREPARED];
        yield [true, RefData::CONTINUATION_DETAIL_STATUS_PRINTED];
        yield [false, RefData::CONTINUATION_DETAIL_STATUS_PRINTING];
        yield [true, RefData::CONTINUATION_DETAIL_STATUS_UNACCEPTABLE];
    }
}
