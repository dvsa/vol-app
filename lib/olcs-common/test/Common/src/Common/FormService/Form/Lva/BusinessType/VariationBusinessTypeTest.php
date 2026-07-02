<?php

declare(strict_types=1);

namespace CommonTest\Common\FormService\Form\Lva\BusinessType;

use Common\FormService\Form\Lva\Variation;
use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\BusinessType\VariationBusinessType;
use Common\FormService\FormServiceInterface;
use Common\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Business Type Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationBusinessTypeTest extends MockeryTestCase
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
     * @var VariationBusinessType
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

        $this->sut = new VariationBusinessType($this->fh, $this->authService, $this->guidanceService, $this->fsm);
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

        $mockVariation = m::mock(Variation::class);
        $mockVariation->expects('alterForm')
            ->with($mockForm);

        $this->fsm->setService('lva-variation', $mockVariation);

        $form = $this->sut->getForm($hasInforceLicences, $hasOrganisationSubmittedLicenceApplication);

        $this->assertSame($mockForm, $form);
    }
}
