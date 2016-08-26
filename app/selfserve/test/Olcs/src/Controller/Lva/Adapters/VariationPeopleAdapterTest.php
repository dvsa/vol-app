<?php

/**
 * Internal / Common Variation People Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\VariationPeopleAdapter;
use Common\Service\Entity\OrganisationEntityService;

/**
 * Internal / Common Variation People Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VariationPeopleAdapterTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        $this->sut = new VariationPeopleAdapter();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testCanModifyWithExceptionalOrganisation()
    {
        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_SOLE_TRADER);

        $this->assertFalse($this->sut->canModify(123));
    }

    public function testAlterFormForExceptionalOrganisation()
    {
        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_SOLE_TRADER);

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
