<?php

namespace OlcsTest\FormService\Form\Lva\TypeOfLicence;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\TypeOfLicence\LicenceTypeOfLicence;
use Zend\Form\Form;
use Common\FormService\FormServiceManager;
use Zend\Form\Element;
use Common\RefData;
use Common\Form\Elements\InputFilters\Lva\BackToLicenceActionLink;

/**
 * Licence Type of Licence Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceTypeOfLicenceTest extends MockeryTestCase
{
    /**
     * @var LicenceTypeOfLicence
     */
    protected $sut;

    protected $fh;

    protected $fsm;

    public function setUp()
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->fsm = m::mock(FormServiceManager::class)->makePartial();
        $this->sut = new LicenceTypeOfLicence();
        $this->sut->setFormHelper($this->fh);
        $this->sut->setFormServiceLocator($this->fsm);
    }

    /**
     * @dataProvider paramsProvider
     */
    public function testAlterForm($params, $removeElement, $accessToLicenceType)
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
                m::mock()
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

    public function paramsProvider()
    {
        return [
            [
                ['canUpdateLicenceType' => true, 'canBecomeSpecialRestricted' => true],
                'form-actions->cancel',
                1
            ],
            [
                ['canUpdateLicenceType' => true, 'canBecomeSpecialRestricted' => false],
                'form-actions->cancel',
                2
            ],
            [
                ['canUpdateLicenceType' => false, 'canBecomeSpecialRestricted' => true],
                'form-actions',
                2
            ],
        ];
    }

    public function mockLockElements($mockForm, $params, $accessToLicenceType)
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

        $mockLicenceType = m::mock(Element::class)
            ->shouldReceive('setLabel')
            ->with('licence-type')
            ->once()
            ->getMock();

        $mockTolFieldset = m::mock()
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
            ->andReturn($mockLicenceType)
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

        if (!$params['canUpdateLicenceType']) {
            $this->fh->shouldReceive('disableElement')
                ->with($mockForm, 'type-of-licence->licence-type')
                ->once()
                ->shouldReceive('lockElement')
                ->with($mockLicenceType, 'licence-type-lock-message')
                ->once()
                ->getMock();

            $mockForm->shouldReceive('has')
                ->with('form-actions')
                ->andReturn(true)
                ->times(3)
                ->shouldReceive('get')
                ->with('form-actions')
                ->andReturn(
                    m::mock()
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
                    m::mock()
                    ->shouldReceive('add')
                    ->with(m::type(BackToLicenceActionLink::class))
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock();
        }
    }
}

