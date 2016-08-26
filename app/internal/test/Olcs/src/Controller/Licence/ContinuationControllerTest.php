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
