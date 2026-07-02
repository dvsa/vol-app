<?php

namespace CommonTest\Common\FormService\Form\Lva\BusinessDetails;

use Common\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\BusinessDetails\VariationBusinessDetails;

/**
 * Variation Business Details Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationBusinessDetailsTest extends MockeryTestCase
{
    protected $sut;

    protected $fsl;

    protected $formHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->fsl = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();

        $this->sut = new VariationBusinessDetails($this->formHelper, $this->fsl);
    }

    public function testAlterForm(): void
    {
        // Params
        $orgType = RefData::ORG_TYPE_REGISTERED_COMPANY;
        $hasInforceLicences = true;
        $hasOrganisationSubmittedLicenceApplication = false;

        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);
        $mockLva = m::mock('\Common\FormService\FormServiceInterface');

        $this->fsl->setService('lva-variation', $mockLva);

        // Expectations
        $mockLva->shouldReceive('alterForm')
            ->with($mockForm);

        $this->formHelper->shouldReceive('createForm')
            ->with('Lva\BusinessDetails')
            ->andReturn($mockForm);

        $form = $this->sut->getForm($orgType, $hasInforceLicences, $hasOrganisationSubmittedLicenceApplication);

        $this->assertSame($mockForm, $form);
    }
}
