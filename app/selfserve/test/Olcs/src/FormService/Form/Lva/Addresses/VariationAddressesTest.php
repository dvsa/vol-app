<?php

namespace OlcsTest\FormService\Form\Lva\Addresses;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\Addresses\VariationAddresses;
use Zend\Form\Form;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Variation Addresses Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VariationAddressesTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var VariationAddresses
     */
    protected $sut;

    protected $fh;

    public function setUp()
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->sut = new VariationAddresses();
        $this->sut->setFormHelper($this->fh);
    }

    public function testAlterForm()
    {
        $mockForm = m::mock(Form::class);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\Addresses')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'establishment')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'establishment_address')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->cancel')
            ->andReturnSelf()
            ->once()
            ->getMock();

        $form = $this->sut->getForm(['typeOfLicence' => ['licenceType' => 'foo']]);

        $this->assertSame($mockForm, $form);
    }
}
