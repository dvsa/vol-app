<?php

namespace OlcsTest\FormService\Form\Lva\Addresses;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\Addresses\ApplicationAddresses;
use Zend\Form\Form;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application Addresses Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationAddressesTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var ApplicationAddresses
     */
    protected $sut;

    protected $fh;

    public function setUp()
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->sut = new ApplicationAddresses();
        $this->sut->setFormHelper($this->fh);
    }

    public function testAlterForm()
    {
        $mockForm = m::mock(Form::class);
        $this->mockAlterButtons($mockForm, $this->fh);

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
            ->getMock();

        $form = $this->sut->getForm(['typeOfLicence' => ['licenceType' => 'foo']]);

        $this->assertSame($mockForm, $form);
    }
}
