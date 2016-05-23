<?php

/**
 * Application Business Details Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva;

use Common\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Olcs\FormService\Form\Lva\ApplicationBusinessDetails;

/**
 * Application Business Details Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBusinessDetailsTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    protected $fsm;

    protected $fh;

    public function setUp()
    {
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();
        $this->sm = Bootstrap::getServiceManager();
        $this->fh = m::mock('\Common\Service\Helper\FormHelperService')->makePartial();

        $this->sut = new ApplicationBusinessDetails();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setFormServiceLocator($this->fsm);
        $this->sut->setFormHelper($this->fh);
    }

    public function testAlterFormWithoutInforceLicences()
    {
        // Params
        $form = m::mock();
        $params = [
            'orgType' => RefData::ORG_TYPE_LLP,
            'hasInforceLicences' =>false
        ];

        // Mocks
        $mockApplicationFormService = m::mock('\Common\FormService\FormServiceInterface');

        $this->fsm->setService('lva-application', $mockApplicationFormService);

        // Expectations
        $mockApplicationFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $this->fh->shouldReceive('remove')
            ->with($form, 'allow-email')
            ->once();

        $this->sut->alterForm($form, $params);
    }

    public function testAlterFormWithInforceLicences()
    {
        // Params
        $form = m::mock();
        $params = [
            'orgType' => RefData::ORG_TYPE_LLP,
            'hasInforceLicences' => true
        ];

        // Mocks
        $mockApplicationFormService = m::mock('\Common\FormService\FormServiceInterface');
        $mockLockBusinessDetailsFormService = m::mock('\Common\FormService\FormServiceInterface');

        $this->fsm->setService('lva-application', $mockApplicationFormService);
        $this->fsm->setService('lva-lock-business_details', $mockLockBusinessDetailsFormService);

        // Expectations
        $mockApplicationFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $this->fh->shouldReceive('remove')
            ->with($form, 'allow-email')
            ->once();

        $mockLockBusinessDetailsFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $this->sut->alterForm($form, $params);
    }
}
