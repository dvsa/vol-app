<?php

/**
 * External Licence People Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\LicencePeopleAdapter;

/**
 * External Licence People Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicencePeopleAdapterTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        $this->sut = new LicencePeopleAdapter();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testAlterFormForOrganisation()
    {
        $form = m::mock('Zend\Form\Form');
        $table = m::mock();

        $this->sm->setService(
            'Lva\People',
            m::mock()
            ->shouldReceive('lockOrganisationForm')
            ->with($form, $table, 123)
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals(
            'foo',
            $this->sut->alterFormForOrganisation($form, $table, 123)
        );
    }

    public function testAlterSoleTraderForm()
    {
        $form = m::mock('Zend\Form\Form');

        $this->sm->setService(
            'Lva\People',
            m::mock()
            ->shouldReceive('lockPersonForm')
            ->with($form)
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals(
            'foo',
            $this->sut->alterSoleTraderFormForOrganisation($form, 123)
        );
    }

    public function testAlterAddOrEditFormForm()
    {
        $form = m::mock('Zend\Form\Form');

        $this->sm->setService(
            'Lva\People',
            m::mock()
            ->shouldReceive('lockPersonForm')
            ->with($form, true)
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals(
            'foo',
            $this->sut->alterAddOrEditFormForOrganisation($form, 123)
        );
    }

    public function testCanModify()
    {
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasInForceLicences')
            ->with(123)
            ->andReturn(true)
            ->getMock()
        );

        $this->assertFalse(
            $this->sut->canModify(123)
        );
    }
}
