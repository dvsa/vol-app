<?php

/**
 * External Application People Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter;

/**
 * External Application People Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ApplicationPeopleAdapterTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        $this->sut = new ApplicationPeopleAdapter();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testAlterFormForOrganisationWithInForceLicences()
    {
        $form = m::mock('Zend\Form\Form');
        $table = m::mock();

        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasInForceLicences')
            ->with(123)
            ->andReturn(true)
            ->getMock()
        );

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

    public function testAlterFormForOrganisationWithNoInForceLicences()
    {
        $form = m::mock('Zend\Form\Form');
        $table = m::mock();

        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasInForceLicences')
            ->with(123)
            ->andReturn(false)
            ->getMock()
        );

        $this->sm->setService(
            'Lva\People',
            m::mock()
            ->shouldReceive('lockOrganisationForm')
            ->never()
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertNull(
            $this->sut->alterFormForOrganisation($form, $table, 123)
        );
    }

    public function testAlterAddOrEditFormFormWithLicences()
    {
        $form = m::mock('Zend\Form\Form');

        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasInForceLicences')
            ->with(123)
            ->andReturn(true)
            ->getMock()
        );

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

    public function testAlterAddOrEditFormFormWithoutLicences()
    {
        $form = m::mock('Zend\Form\Form');

        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasInForceLicences')
            ->with(123)
            ->andReturn(false)
            ->getMock()
        );

        $this->sm->setService(
            'Lva\People',
            m::mock()
            ->shouldReceive('lockPersonForm')
            ->never()
            ->getMock()
        );

        $this->assertNull(
            $this->sut->alterAddOrEditFormForOrganisation($form, 123)
        );
    }

    public function testCanModifyWithLicences()
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

    public function testCanModifyWithoutLicences()
    {
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasInForceLicences')
            ->with(123)
            ->andReturn(false)
            ->getMock()
        );

        $this->assertTrue(
            $this->sut->canModify(123)
        );
    }
}
