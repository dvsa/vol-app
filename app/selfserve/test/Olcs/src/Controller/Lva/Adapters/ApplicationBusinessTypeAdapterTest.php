<?php

/**
 * External Application Business Type Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\ApplicationBusinessTypeAdapter;
use Common\Service\Entity\LicenceEntityService;

/**
 * External Application Business Type Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ApplicationBusinessTypeAdapterTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->controller = m::mock('\Zend\Mvc\Controller\AbstractController');

        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        $this->sut = new ApplicationBusinessTypeAdapter();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testAlterFormWithInForceLicences()
    {
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasInForceLicences')
            ->with(123)
            ->andReturn(true)
            ->getMock()
        );

        $this->sm->setService(
            'Lva\BusinessType',
            m::mock()
            ->shouldReceive('lockType')
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals(
            'foo',
            $this->sut->alterFormForOrganisation(m::mock('Zend\Form\Form'), 123)
        );
    }

    public function testAlterFormWithNoInForceLicences()
    {
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasInForceLicences')
            ->with(123)
            ->andReturn(false)
            ->getMock()
        );

        $this->sm->setService(
            'Lva\BusinessType',
            m::mock()
            ->shouldReceive('lockType')
            ->never()
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertNull(
            $this->sut->alterFormForOrganisation(m::mock('Zend\Form\Form'), 123)
        );
    }
}
