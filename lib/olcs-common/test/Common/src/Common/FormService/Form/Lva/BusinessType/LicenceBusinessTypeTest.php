<?php

declare(strict_types=1);

namespace CommonTest\Common\FormService\Form\Lva\BusinessType;

use Common\FormService\Form\Lva\Licence;
use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\BusinessType\LicenceBusinessType;
use Common\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

class LicenceBusinessTypeTest extends MockeryTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $authService;
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $guidanceService;
    /**
     * @var LicenceBusinessType
     */
    protected $sut;

    protected $fsm;

    protected $fh;

    #[\Override]
    protected function setUp(): void
    {
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->authService = m::mock(AuthorizationService::class);
        $this->guidanceService = m::mock(\Common\Service\Helper\GuidanceHelperService::class);

        $this->sut = new LicenceBusinessType($this->fh, $this->authService, $this->guidanceService, $this->fsm);
    }

    public function testGetForm(): void
    {
        $hasInforceLicences = true;
        $hasOrganisationSubmittedLicenceApplication = false;

        $mockForm = m::mock(Form::class);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\BusinessType')
            ->andReturn($mockForm);

        $mockLicence = m::mock(Licence::class);
        $mockLicence->expects('alterForm')
            ->with($mockForm);

        $this->fsm->setService('lva-licence', $mockLicence);

        $form = $this->sut->getForm($hasInforceLicences, $hasOrganisationSubmittedLicenceApplication);

        $this->assertSame($mockForm, $form);
    }
}
