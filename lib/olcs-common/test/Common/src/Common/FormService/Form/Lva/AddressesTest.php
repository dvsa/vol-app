<?php

declare(strict_types=1);

namespace CommonTest\Common\FormService\Form\Lva;

use Common\Form\Elements\Types\Address as AddressFieldset;
use Common\RefData;
use Laminas\Form\ElementInterface;
use Laminas\Form\FieldsetInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\Addresses;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\FormService\Form\Lva\Addresses::class)]
final class AddressesTest extends MockeryTestCase
{
    /** @var  Addresses */
    protected $sut;

    /** @var  m\MockInterface */
    private $formHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);

        $this->sut = new Addresses($this->formHelper);
    }

    public function testAlterForm(): void
    {
        $form = m::mock(\Laminas\Form\Form::class);

        $this->formHelper
            ->shouldReceive('createForm')->once()->with('Lva\Addresses')->andReturn($form)
            ->shouldReceive('remove')->once()->with($form, 'establishment')->andReturnSelf()
            ->shouldReceive('remove')->once()->with($form, 'establishment_address');

        $form->expects('has')->with('establishment_address')->andReturn(false);
        $this->expectConsultantAddressUsesNamesAsIds($form);

        $this->assertEquals(
            $form,
            $this->sut->getForm(
                [
                    'typeOfLicence' => [
                        'licenceType' => RefData::LICENCE_TYPE_RESTRICTED,
                    ],
                ]
            )
        );
    }

    public function testAlterFormLabelsEstablishmentAddress(): void
    {
        $form = m::mock(\Laminas\Form\Form::class);
        $address = m::mock(FieldsetInterface::class);

        $this->formHelper->expects('createForm')->with('Lva\Addresses')->andReturn($form);
        $this->formHelper->shouldNotReceive('remove');

        $form->expects('has')->with('establishment_address')->andReturn(true);
        $form->expects('get')->with('establishment_address')->andReturn($address);

        $labels = [
            'addressLine1' => 'lva.establishment-address.address-line-1',
            'addressLine2' => 'lva.establishment-address.address-line-2',
            'addressLine3' => 'lva.establishment-address.address-line-3',
            'addressLine4' => 'lva.establishment-address.address-line-4',
            'town' => 'lva.establishment-address.town-city',
            'postcode' => 'lva.establishment-address.postcode',
            'countryCode' => 'lva.establishment-address.country',
        ];
        foreach ($labels as $name => $label) {
            $element = m::mock(ElementInterface::class);
            $element->expects('setLabel')->with($label)->andReturnSelf();
            $address->expects('get')->with($name)->andReturn($element);
        }
        $this->expectConsultantAddressUsesNamesAsIds($form);

        $this->assertSame(
            $form,
            $this->sut->getForm(
                [
                    'typeOfLicence' => [
                        'licenceType' => RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                    ],
                ]
            )
        );
    }

    private function expectConsultantAddressUsesNamesAsIds(m\MockInterface $form): void
    {
        $consultantAddress = m::mock(AddressFieldset::class);
        $consultantAddress->expects('useNamesAsIds')->withNoArgs()->andReturnNull();
        $form->expects('get')->with('consultantAddress')->andReturn($consultantAddress);
    }
}
