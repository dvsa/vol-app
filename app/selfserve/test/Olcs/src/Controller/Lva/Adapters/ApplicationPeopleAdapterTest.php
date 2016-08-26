<?php

/**
 * Internal / Common Application People Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter;
use Common\Service\Entity\OrganisationEntityService;

/**
 * Internal / Common Application People Adapter Test
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

    public function testAlterFormForOrganisationDoesNotAlterFormWithoutInForceLicences()
    {
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasInForceLicences')
            ->with(123)
            ->andReturn(false)
            ->getMock()
        );

        $form = m::mock('Zend\Form\Form');
        $table = m::mock();

        $this->assertNull($this->sut->alterFormForOrganisation($form, $table, 123));
    }

    public function testAlterAddOrEditFormForOrganisationDoesNotAlterFormWithInForceLicences()
    {
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasInForceLicences')
            ->with(123)
            ->andReturn(true)
            ->shouldReceive('getType')
            ->with(123)
            ->andReturn(
                [
                    'type' => [
                        'id' => OrganisationEntityService::ORG_TYPE_OTHER
                    ]
                ]
            )
            ->getMock()
        );

        $form = m::mock('Zend\Form\Form');

        $this->assertNull($this->sut->alterAddOrEditFormForOrganisation($form, 123));
    }

    public function testAlterAddOrEditFormForOrganisationDoesNotAlterFormWithoutInForceLicences()
    {
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasInForceLicences')
            ->with(123)
            ->andReturn(false)
            ->getMock()
        );

        $form = m::mock('Zend\Form\Form');

        $this->assertNull($this->sut->alterAddOrEditFormForOrganisation($form, 123));
    }

    public function testCanModifyWithNoInForceLicences()
    {
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasInForceLicences')
            ->with(123)
            ->andReturn(false)
            ->getMock()
        );

        $this->assertTrue($this->sut->canModify(123));
    }

    public function testCanModifyNormalOrganisation()
    {
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasInForceLicences')
            ->with(123)
            ->andReturn(true)
            ->shouldReceive('getType')
            ->with(123)
            ->andReturn(
                [
                    'type' => [
                        'id' => OrganisationEntityService::ORG_TYPE_OTHER
                    ]
                ]
            )
            ->getMock()
        );

        $this->assertTrue($this->sut->canModify(123));
    }
}
