<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\TypeOfLicence;

use Common\Service\Helper\FormHelperService;
use Laminas\Form\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\TypeOfLicence\LicenceTypeOfLicence;
use Laminas\Form\Form;
use Common\FormService\FormServiceManager;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Common\RefData;
use Common\Form\Elements\InputFilters\Lva\BackToLicenceActionLink;
use LmcRbacMvc\Service\AuthorizationService;

class LicenceTypeOfLicenceTest extends MockeryTestCase
{
    /**
     * @var LicenceTypeOfLicence
     */
    protected $sut;

    protected $fh;

    protected $fsm;

    public function setUp(): void
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->fsm = m::mock(FormServiceManager::class)->makePartial();
        $this->sut = new LicenceTypeOfLicence($this->fh, m::mock(AuthorizationService::class), $this->fsm);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('paramsProvider')]
    public function testAlterForm(array $params, string $removeElement, int $accessToLicenceType): void
    {

        $mockForm = m::mock(Form::class);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\TypeOfLicence')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, $removeElement)
            ->getMock();

        $this->fsm->shouldReceive('get')
            ->with('lva-licence')
            ->once()
            ->andReturn(
                m::mock(ElementInterface::class)
                ->shouldReceive('alterForm')
                ->with($mockForm)
                ->once()
                ->getMock()
            )
            ->getMock();

        $this->mockLockElements($mockForm, $params, $accessToLicenceType);

        $form = $this->sut->getForm($params);

        $this->assertSame($mockForm, $form);
    }

    /**
     * @return (bool[]|int|string)[][]
     *
     * @psalm-return list{list{array{canUpdateLicenceType: false, canBecomeSpecialRestricted: true}, 'form-actions', 2}, list{array{canUpdateLicenceType: false, canBecomeSpecialRestricted: false}, 'form-actions', 3}}
     */
    public static function paramsProvider(): array
    {
        return [
            [
                ['canUpdateLicenceType' => false, 'canBecomeSpecialRestricted' => true],
                'form-actions',
                2
            ],
            [
                ['canUpdateLicenceType' => false, 'canBecomeSpecialRestricted' => false],
                'form-actions',
                3
            ],
        ];
    }

    public function mockLockElements(m\LegacyMockInterface $mockForm, array $params, int $accessToLicenceType): void
    {
        $mockOperatorLocation = m::mock(Element::class)
            ->shouldReceive('setLabel')
            ->with('operator-location')
            ->once()
            ->getMock();

        $mockOperatorType = m::mock(Element::class)
            ->shouldReceive('setLabel')
            ->with('operator-type')
            ->once()
            ->getMock();

        $mockLicenceType = m::mock(Element::class);

        $ltFieldset = m::mock(Fieldset::class);
        $ltFieldset->shouldReceive('get')
            ->with('licence-type')
            ->andReturn($mockLicenceType);
        $ltFieldset->shouldReceive('setLabel')
            ->with('licence-type')
            ->once();

        $mockTolFieldset = m::mock(ElementInterface::class)
            ->shouldReceive('get')
            ->with('operator-location')
            ->andReturn($mockOperatorLocation)
            ->twice()
            ->shouldReceive('get')
            ->with('operator-type')
            ->andReturn($mockOperatorType)
            ->twice()
            ->shouldReceive('get')
            ->with('licence-type')
            ->andReturn($ltFieldset)
            ->times($accessToLicenceType)
            ->getMock();

        $mockForm->shouldReceive('get')
            ->with('type-of-licence')
            ->once()
            ->andReturn($mockTolFieldset)
            ->getMock();

        $this->fh->shouldReceive('lockElement')
            ->with($mockOperatorLocation, 'operator-location-lock-message')
            ->once()
            ->shouldReceive('lockElement')
            ->with($mockOperatorType, 'operator-type-lock-message')
            ->once()
            ->shouldReceive('disableElement')
            ->with($mockForm, 'type-of-licence->operator-location')
            ->once()
            ->shouldReceive('disableElement')
            ->with($mockForm, 'type-of-licence->operator-type')
            ->once()
            ->getMock();

        if (!$params['canBecomeSpecialRestricted']) {
            $this->fh->shouldReceive('removeOption')
                ->with($mockLicenceType, RefData::LICENCE_TYPE_SPECIAL_RESTRICTED)
                ->once()
                ->getMock();
        }

        $this->fh->shouldReceive('disableElement')
            ->with($mockForm, 'type-of-licence->licence-type->licence-type')
            ->once()
            ->shouldReceive('disableElement')
            ->with($mockForm, 'type-of-licence->licence-type->ltyp_siContent->vehicle-type')
            ->once()
            ->shouldReceive('disableElement')
            ->with(
                $mockForm,
                'type-of-licence->licence-type->ltyp_siContent->lgv-declaration->lgv-declaration-confirmation'
            )
            ->once()
            ->shouldReceive('lockElement')
            ->with($ltFieldset, 'licence-type-lock-message')
            ->once()
            ->getMock();

        $mockForm->shouldReceive('has')
            ->with('form-actions')
            ->andReturn(true)
            ->times(3)
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock(ElementInterface::class)
                ->shouldReceive('has')
                ->with('save')
                ->once()
                ->andReturn(true)
                ->shouldReceive('has')
                ->with('saveAndContinue')
                ->once()
                ->andReturn(true)
                ->shouldReceive('has')
                ->with('cancel')
                ->once()
                ->andReturn(true)
                ->shouldReceive('remove')
                ->with('save')
                ->once()
                ->shouldReceive('remove')
                ->with('saveAndContinue')
                ->once()
                ->shouldReceive('remove')
                ->with('cancel')
                ->once()
                ->getMock()
            )
            ->times(3)
            ->getMock();

        $mockForm->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock(ElementInterface::class)
                ->shouldReceive('add')
                ->with(m::type(BackToLicenceActionLink::class))
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();
    }
}
