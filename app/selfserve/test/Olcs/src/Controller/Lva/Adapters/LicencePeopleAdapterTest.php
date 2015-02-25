<?php

/**
 * Internal / Common Licence People Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\LicencePeopleAdapter;
use Common\Service\Entity\OrganisationEntityService;

/**
 * Internal / Common Licence People Adapter Test
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

    public function testAddMessagesWithExceptionalOrganisation()
    {
        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_SOLE_TRADER);

        $this->sm->setService(
            'Lva\LicencePeople',
            m::mock()
            ->shouldReceive('addVariationMessage')
            ->never()
            ->getMock()
        );

        $this->sut->addMessages(123);
    }

    public function testAddMessagesWithNormalOrganisation()
    {
        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_OTHER);

        $controller = m::mock('Zend\Mvc\Controller\AbstractController');

        $this->sm->setService(
            'Lva\LicencePeople',
            m::mock()
            ->shouldReceive('addVariationMessage')
            ->with($controller)
            ->getMock()
        );

        $this->sut->setController($controller);

        $this->sut->addMessages(123);
    }

    public function testAlterFormForOrganisation()
    {
        $form = m::mock('Zend\Form\Form');
        $table = m::mock();

        $this->sm->setService(
            'Lva\People',
            m::mock()
            ->shouldReceive('lockOrganisationForm')
            ->with($form, $table)
            ->getMock()
        );

        $this->sut->alterFormForOrganisation($form, $table, 123);
    }

    public function testAlterAddOrEditForOrganisation()
    {
        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_OTHER);

        $form = m::mock('Zend\Form\Form');

        $this->sm->setService(
            'Lva\People',
            m::mock()
            ->shouldReceive('lockPersonForm')
            ->with($form, OrganisationEntityService::ORG_TYPE_OTHER)
            ->getMock()
        );

        $this->sut->alterAddOrEditFormForOrganisation($form, 123);
    }

    public function testCanModify()
    {
        $this->assertFalse($this->sut->canModify(123));
    }

    private function mockOrg($id, $type)
    {
        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('getType')
            ->with($id)
            ->andReturn(
                [
                    'type' => [
                        'id' => $type
                    ]
                ]
            )
            ->getMock()
        );
    }
}
