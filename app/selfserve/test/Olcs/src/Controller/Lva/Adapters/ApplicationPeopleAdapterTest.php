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

    public function testAlterFormForOrganisationDoesNotAlterFormWithInForceLicences()
    {
        $this->markTestSkipped();

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
        $table = m::mock();

        $this->assertNull($this->sut->alterFormForOrganisation($form, $table, 123));
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

    public function testCanModifyExceptionalOrganisation()
    {
        $this->markTestSkipped();

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
                        'id' => OrganisationEntityService::ORG_TYPE_SOLE_TRADER
                    ]
                ]
            )
            ->getMock()
        );

        $this->assertFalse($this->sut->canModify(123));
    }

    public function testRestoreWithExceptionalOrganisation()
    {
        $this->markTestSkipped();

        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_SOLE_TRADER);

        try {
            $this->sut->restore(123, 5);
        } catch (\Exception $e) {
            $this->assertEquals('Not implemented', $e->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    public function testRestoreNotRequiringDeltas()
    {
        $this->markTestSkipped();

        $this->mockIdentifier(456);

        $this->sm->setService(
            'Entity\ApplicationOrganisationPerson',
            m::mock()
            ->shouldReceive('getAllByApplication')
            ->with(456, 1)
            ->andReturn(['Count' => 0])
            ->getMock()
        );

        $this->sm->setService(
            'Entity\Organisation',
            m::mock()
            ->shouldReceive('hasInForceLicences')
            ->with(123)
            ->andReturn(false)
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

        try {
            $this->sut->restore(123, 5);
        } catch (\Exception $e) {
            $this->assertEquals('Not implemented', $e->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
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

    private function mockIdentifier($identifier)
    {
        $this->sm->setService(
            'ApplicationLvaAdapter',
            m::mock()
            ->shouldReceive('getIdentifier')
            ->andReturn($identifier)
            ->shouldReceive('setController')
            ->getMock()
        );
    }
}
