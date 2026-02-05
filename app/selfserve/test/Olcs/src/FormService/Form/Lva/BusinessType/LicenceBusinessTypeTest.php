<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\BusinessType;

use Common\Form\Elements\InputFilters\Lva\BackToLicenceActionLink;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\BusinessType\LicenceBusinessType;
use Laminas\Form\Form;
use Laminas\Form\Element;
use LmcRbacMvc\Service\AuthorizationService;

class LicenceBusinessTypeTest extends MockeryTestCase
{
    /**
     * @var LicenceBusinessType
     */
    protected $sut;

    protected $fsm;

    protected $fh;

    private $guidanceHelper;

    private $authService;

    public function setUp(): void
    {
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->authService = m::mock(AuthorizationService::class);
        $this->guidanceHelper = m::mock(\Common\Service\Helper\GuidanceHelperService::class);
        $this->sut = new LicenceBusinessType($this->fh, $this->authService, $this->guidanceHelper, $this->fsm);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetForm')]
    public function testGetForm(bool $hasInforceLicences, bool $hasOrganisationSubmittedLicenceApplication): void
    {
        $mockElement = m::mock(Element::class);

        $formActions = m::mock(ElementInterface::class);
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);

        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');
        $formActions->shouldReceive('remove')->once()->with('cancel');

        $formActions->shouldReceive('add')->once()->with(m::type(BackToLicenceActionLink::class));

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')->with('data')
            ->andReturn(
                m::mock(ElementInterface::class)->shouldReceive('get')
                    ->with('type')
                    ->andReturn($mockElement)
                    ->getMock()
            );

        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\BusinessType')
            ->andReturn($mockForm)
            ->shouldReceive('lockElement')
            ->once()
            ->with($mockElement, 'business-type.locked')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'data->type')
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions')
            ->once()
            ->getMock();

        $mockLicence = m::mock(Form::class);
        $mockLicence->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $this->guidanceHelper
                    ->shouldReceive('append')
                    ->with('business-type.locked.message')
                    ->once();

        $this->fsm->setService('lva-licence', $mockLicence);

        $form = $this->sut->getForm($hasInforceLicences, $hasOrganisationSubmittedLicenceApplication);

        $this->assertSame($mockForm, $form);
    }

    /**
     * @return bool[][]
     *
     * @psalm-return list{list{true, true}, list{true, false}, list{false, true}, list{false, false}}
     */
    public static function dpGetForm(): array
    {
        return [
            [
                true, true
            ],
            [
                true, false
            ],
            [
                false, true
            ],
            [
                false, false
            ]
        ];
    }
}
