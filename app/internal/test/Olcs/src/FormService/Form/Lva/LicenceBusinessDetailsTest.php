<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\LicenceBusinessDetails;

class LicenceBusinessDetailsTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    protected $fsm;

    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class)->makePartial();
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();

        $this->sut = new LicenceBusinessDetails($this->formHelper, $this->fsm);
    }

    public function testAlterForm(): void
    {
        $mockForm = m::mock();

        $params = [
            'orgType' => 'foo',
            'isLicenseApplicationPsv' => false,
        ];

        $this->fsm->shouldReceive('get')
            ->with('lva-licence')
            ->andReturn(
                m::mock()
                ->shouldReceive('alterForm')
                ->with($mockForm)
                ->once()
                ->getMock()
            );

        $this->formHelper->shouldReceive('remove')
            ->with($mockForm, 'allow-email')
            ->once();

        $this->sut->alterForm($mockForm, $params);
    }
}
