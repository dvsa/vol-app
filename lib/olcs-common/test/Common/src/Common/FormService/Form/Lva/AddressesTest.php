<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Common\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\Addresses;

/**
 * @covers Common\FormService\Form\Lva\Addresses
 */
class AddressesTest extends MockeryTestCase
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
}
