<?php

/**
 * Variation Business Details Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\VariationBusinessDetails;
use Common\Service\Entity\OrganisationEntityService;

/**
 * Variation Business Details Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationBusinessDetailsTest extends MockeryTestCase
{
    protected $sut;

    protected $fsm;

    protected $fh;

    public function setUp()
    {
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = new VariationBusinessDetails();
        $this->sut->setFormServiceLocator($this->fsm);
        $this->fh = m::mock('\Common\Service\Helper\FormHelperService')->makePartial();
        $this->sut->setFormHelper($this->fh);
    }

    public function testAlterForm()
    {
        // Params
        $form = m::mock();
        $params = [
            'orgId' => 111,
            'orgType' => OrganisationEntityService::ORG_TYPE_LLP
        ];

        // Mocks
        $mockApplicationFormService = m::mock('\Common\FormService\FormServiceInterface');
        $mockLockBusinessDetailsFormService = m::mock('\Common\FormService\FormServiceInterface');

        $this->fsm->setService('lva-variation', $mockApplicationFormService);
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
