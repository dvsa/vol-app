<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\Form\Elements\Types\Address as AddressFieldset;
use Common\RefData;
use Laminas\Form\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\Addresses\LicenceAddresses;
use Laminas\Form\Form;

final class LicenceAddressesTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    #[\Override]
    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class)->makePartial();

        $this->sut = new LicenceAddresses($this->formHelper);
    }

    public function testGetForm(): void
    {
        $formActions = m::mock(ElementInterface::class);
        $formActions->shouldReceive('get')
            ->with('save')
            ->andReturn(
                m::mock(ElementInterface::class)
                ->shouldReceive('setAttribute')
                ->with('class', 'govuk-button')
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $mockForm = m::mock(Form::class);
        // the establishment address labels and consultant address ids are covered by the common Addresses test
        $mockForm->expects('has')->with('establishment_address')->andReturn(false);
        $consultantAddress = m::mock(AddressFieldset::class);
        $consultantAddress->expects('useNamesAsIds')->withNoArgs()->andReturnNull();
        $mockForm->expects('get')->with('consultantAddress')->andReturn($consultantAddress);
        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper
            ->shouldReceive('createForm')
            ->with('Lva\Addresses')
            ->andReturn($mockForm)
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->cancel')
            ->once()
            ->getMock();

        $this->sut->getForm(
            ['typeOfLicence' => ['licenceType' => RefData::LICENCE_TYPE_STANDARD_NATIONAL]]
        );
    }
}
