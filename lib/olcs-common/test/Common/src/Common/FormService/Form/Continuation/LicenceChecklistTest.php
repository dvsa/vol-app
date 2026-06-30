<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Common\Service\Helper\UrlHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Continuation\LicenceChecklist;
use Common\Form\Elements\Types\CheckboxAdvanced;
use Common\Form\Model\Form\Continuation\LicenceChecklist as LicenceChecklistForm;
use Common\Service\Helper\FormHelperService;
use Common\RefData;
use Common\FormService\FormServiceManager;
use Laminas\Form\Form;

/**
 * Licence checklist form service test
 */
class LicenceChecklistTest extends MockeryTestCase
{
    /** @var LicenceChecklist */
    protected $sut;

    /** @var  m\MockInterface */
    private $formHelper;

    /** @var  m\MockInterface */
    protected $urlHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new LicenceChecklist($this->formHelper, $this->urlHelper);
    }

    public function testAlterForm(): void
    {
        $form = m::mock(LicenceChecklistForm::class)
            ->shouldReceive('get')
            ->with('data')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('peopleCheckbox')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getLabel')
                            ->andReturn('label.')
                            ->once()
                            ->shouldReceive('setLabel')
                            ->with('label.' . RefData::ORG_TYPE_REGISTERED_COMPANY)
                            ->once()
                            ->shouldReceive('getOption')
                            ->with('not_checked_message')
                            ->andReturn('message.')
                            ->once()
                            ->shouldReceive('setOption')
                            ->with('not_checked_message', 'message.'  . RefData::ORG_TYPE_REGISTERED_COMPANY)
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('get')
            ->with('licenceChecklistConfirmation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('noContent')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('get')
                            ->with('backToLicence')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('setValue')
                                    ->with('url')
                                    ->once()
                                    ->getMock()
                            )
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('get')
                    ->with('yesContent')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('get')
                            ->with('submit')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('setLabel')
                                    ->with('continuations.checklist.confirmation.yes-button-declaration')
                                    ->once()
                                    ->getMock()
                            )
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->twice()
            ->getMock();

        $this->urlHelper->shouldReceive('fromRoute')
            ->with(
                'lva-licence',
                ['licence' => 2]
            )
            ->andReturn('url')
            ->once()
            ->getMock();

        $this->formHelper
            ->shouldReceive('createForm')
            ->with(LicenceChecklistForm::class)
            ->andReturn($form)
            ->once()
            ->getMock();

        $data = [
            'licence' => [
                'organisation' => [
                    'type' => [
                        'id' => RefData::ORG_TYPE_REGISTERED_COMPANY
                    ],
                    'organisationPersons' => ['foo']
                ],
                'licenceVehicles' => [
                    ['vehicle' => 'foo'],
                ],
                'id' => 2,
                'licenceType' => [
                    'id' => RefData::LICENCE_TYPE_SPECIAL_RESTRICTED
                ],
                'vehicleType' => [
                    'id' => RefData::APP_VEHICLE_TYPE_PSV
                ],
                'goodsOrPsv' => [
                    'id' => RefData::LICENCE_CATEGORY_PSV
                ]
            ],
            'id' => 1,
            'sections' => [
                'typeOfLicence',
                'businessType',
                'businessDetails',
                'addresses',
                'people',
                'operatingCentres',
                'transportManagers',
                'vehiclesPsv',
                'safety',
                'users'
            ]
        ];
        $this->assertEquals($form, $this->sut->getForm($data));
    }

    /**
     * @dataProvider dpAlterOperatingCentresSection
     */
    public function testAlterOperatingCentresSection($vehicleTypeId, $updatedLabel, $updatedNotCheckedMessage): void
    {
        $operatingCentresCheckbox = m::mock(CheckboxAdvanced::class)->makePartial();
        $operatingCentresCheckbox->setLabel('existing-label');
        $operatingCentresCheckbox->setOption('not_checked_message', 'existing-not-checked-message');

        $form = m::mock(Form::class);
        $form->shouldReceive('get')
            ->with('data')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('operatingCentresCheckbox')
            ->andReturn($operatingCentresCheckbox);

        $sut = m::mock(LicenceChecklist::class)->makePartial();
        $sut->alterOperatingCentresSection($form, $vehicleTypeId);

        $this->assertEquals(
            $updatedLabel,
            $operatingCentresCheckbox->getLabel()
        );

        $this->assertEquals(
            $updatedNotCheckedMessage,
            $operatingCentresCheckbox->getOption('not_checked_message')
        );
    }

    /**
     * @return string[][]
     *
     * @psalm-return list{list{'app_veh_type_psv', 'existing-label', 'existing-not-checked-message'}, list{'app_veh_type_hgv', 'existing-label', 'existing-not-checked-message'}, list{'app_veh_type_mixed', 'existing-label', 'existing-not-checked-message'}, list{'app_veh_type_lgv', 'existing-label.lgv', 'existing-not-checked-message.lgv'}}
     */
    public function dpAlterOperatingCentresSection(): array
    {
        return [
            [
                RefData::APP_VEHICLE_TYPE_PSV,
                'existing-label',
                'existing-not-checked-message'
            ],
            [
                RefData::APP_VEHICLE_TYPE_HGV,
                'existing-label',
                'existing-not-checked-message'
            ],
            [
                RefData::APP_VEHICLE_TYPE_MIXED,
                'existing-label',
                'existing-not-checked-message'
            ],
            [
                RefData::APP_VEHICLE_TYPE_LGV,
                'existing-label.lgv',
                'existing-not-checked-message.lgv'
            ],
        ];
    }

    public function testAlterContinueButton(): void
    {
        $sut = m::mock(LicenceChecklist::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $form = m::mock()
            ->shouldReceive('get')
            ->with('licenceChecklistConfirmation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('yesContent')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('get')
                            ->with('submit')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('setLabel')
                                    ->with(
                                        'continuations.checklist.confirmation.yes-button-conditions-undertakings'
                                    )
                                    ->once()
                                    ->getMock()
                            )
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $sut->alterContinueButton(
            $form,
            [
                'licence' => ['licenceType' => ['id' => RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL]],
                'hasConditionsUndertakings' => true
            ]
        );
    }
}
