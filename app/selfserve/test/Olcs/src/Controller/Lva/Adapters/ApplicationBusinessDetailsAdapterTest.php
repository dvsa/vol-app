<?php

/**
 * External Application Business Details Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\ApplicationBusinessDetailsAdapter;
use Common\Service\Entity\LicenceEntityService;
use OlcsTest\Bootstrap;

/**
 * External Application Business Details Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ApplicationBusinessDetailsAdapterTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->controller = m::mock('\Zend\Mvc\Controller\AbstractController');

        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        $this->sut = new ApplicationBusinessDetailsAdapter();
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
            'Lva\BusinessDetails',
            m::mock()
            ->shouldReceive('lockDetails')
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
            'Lva\BusinessDetails',
            m::mock()
            ->shouldReceive('lockDetails')
            ->never()
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertNull(
            $this->sut->alterFormForOrganisation(m::mock('Zend\Form\Form'), 123)
        );
    }

    public function testHasChangedTradingNames()
    {
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasChangedTradingNames')
            ->with(123, [])
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals('foo', $this->sut->hasChangedTradingNames(123, []));
    }

    public function testHasChangedNatureOfBusiness()
    {
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasChangedNatureOfBusiness')
            ->with(123, [])
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals('foo', $this->sut->hasChangedNatureOfBusiness(123, []));
    }

    public function testHasChangedRegisteredAddress()
    {
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasChangedRegisteredAddress')
            ->with(123, [])
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals('foo', $this->sut->hasChangedRegisteredAddress(123, []));
    }

    public function testHasChangedSubsidiaryCompany()
    {
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasChangedSubsidiaryCompany')
            ->with(123, [])
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals('foo', $this->sut->hasChangedSubsidiaryCompany(123, []));
    }

    public function testPostSave()
    {
        $this->sm->setService(
            'Lva\BusinessDetails',
            m::mock()
            ->shouldReceive('createChangeTask')
            ->with([])
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals('foo', $this->sut->postSave([]));
    }

    public function testPostCrudSave()
    {
        $this->sm->setService(
            'Lva\BusinessDetails',
            m::mock()
            ->shouldReceive('createSubsidiaryChangeTask')
            ->with('save', [])
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals('foo', $this->sut->postCrudSave('save', []));
    }
}
