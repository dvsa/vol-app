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

    public function testCanModifyWithoutExceptionalOrganisation()
    {
        $this->markTestSkipped();

        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_OTHER);

        $this->assertTrue($this->sut->canModify(123));
    }

    public function testCreateTableNotRequiringDeltas()
    {
        $this->markTestSkipped();

        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_SOLE_TRADER);

        $this->sm->setService(
            'Entity\Person',
            m::mock()
            ->shouldReceive('getAllForOrganisation')
            ->with(123)
            ->andReturn(
                [
                    'Results' => [],
                    'Count' => 0
                ]
            )
            ->getMock()
        );

        $this->sm->setService(
            'Table',
            m::mock()
            ->shouldReceive('prepareTable')
            ->with('lva-people', [])
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals('foo', $this->sut->createTable(123));
    }

    public function testCreateTableRequiringDeltas()
    {
        $this->markTestSkipped();

        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_OTHER);

        $this->mockIdentifier(456);

        $rawData = [
            [
                'position' => 'a position',
                'action' => 'U',
                'person' => [
                    'forename' => 'Name',
                    'familyName' => 'Surname'
                ]
            ]
        ];

        $this->sm->setService(
            'Lva\VariationPeople',
            m::mock()
            ->shouldReceive('getTableData')
            ->with(123, 456)
            ->andReturn($rawData)
            ->getMock()
        );

        $formattedData = [
            [
                'position' => 'a position',
                'action' => 'U',
                'forename' => 'Name',
                'familyName' => 'Surname'
            ]
        ];
        $this->sm->setService(
            'Table',
            m::mock()
            ->shouldReceive('prepareTable')
            ->with('lva-variation-people', $formattedData)
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals('foo', $this->sut->createTable(123));
    }

    public function testDeleteNotRequiringDeltas()
    {
        $this->markTestSkipped();

        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_SOLE_TRADER);

        $this->sm->setService(
            'Entity\OrganisationPerson',
            m::mock()
            ->shouldReceive('deleteByOrgAndPersonId')
            ->with(123, 5)
            ->shouldReceive('getAllWithPerson')
            ->with(5)
            ->andReturn(['Count' => 0])
            ->getMock()
        );

        $this->sm->setService(
            'Entity\Person',
            m::mock()
            ->shouldReceive('delete')
            ->with(5)
            ->getMock()
        );

        $this->sut->delete(123, 5);
    }

    public function testDeleteRequiringDeltas()
    {
        $this->markTestSkipped();

        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_OTHER);

        $this->mockIdentifier(456);

        $this->sm->setService(
            'Lva\VariationPeople',
            m::mock()
            ->shouldReceive('deletePerson')
            ->with(123, 5, 456)
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals('foo', $this->sut->delete(123, 5));
    }

    public function testRestoreNotRequiringDeltas()
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

    public function testRestoreRequiringDeltas()
    {
        $this->markTestSkipped();

        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_OTHER);

        $this->mockIdentifier(456);

        $this->sm->setService(
            'Lva\VariationPeople',
            m::mock()
            ->shouldReceive('restorePerson')
            ->with(123, 5, 456)
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals('foo', $this->sut->restore(123, 5));
    }

    public function testAddNotRequiringDeltas()
    {
        $this->markTestSkipped();

        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_SOLE_TRADER);

        $data = [
            'position' => 'a position'
        ];

        $person = [
            'id' => 10
        ];

        $this->sm->setService(
            'Entity\Person',
            m::mock()
            ->shouldReceive('save')
            ->with($data)
            ->andReturn($person)
            ->getMock()
        );

        $this->sm->setService(
            'Entity\OrganisationPerson',
            m::mock()
            ->shouldReceive('save')
            ->with(
                [
                    'organisation' => 123,
                    'person' => 10,
                    'position' => 'a position'
                ]
            )
            ->getMock()
        );

        $this->sut->save(123, $data);
    }

    public function testAddRequiringDeltas()
    {
        $this->markTestSkipped();

        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_OTHER);

        $this->mockIdentifier(456);

        $this->sm->setService(
            'Lva\VariationPeople',
            m::mock()
            ->shouldReceive('savePerson')
            ->with(123, 5, 456)
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals('foo', $this->sut->save(123, 5));
    }

    public function testUpdateNotRequiringDeltas()
    {
        $this->markTestSkipped();

        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_SOLE_TRADER);

        $data = [
            'position' => 'a position',
            'id' => 100
        ];

        $this->sm->setService(
            'Entity\Person',
            m::mock()
            ->shouldReceive('save')
            ->with($data)
            ->getMock()
        );

        $this->sut->save(123, $data);
    }

    public function testGetPersonPositionNotRequiringDeltas()
    {
        $this->markTestSkipped();

        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_SOLE_TRADER);

        $this->sm->setService(
            'Entity\OrganisationPerson',
            m::mock()
            ->shouldReceive('getByOrgAndPersonId')
            ->with(123, 5)
            ->andReturn(
                [
                    'position' => 'a position'
                ]
            )
            ->getMock()
        );

        $this->assertEquals('a position', $this->sut->getPersonPosition(123, 5));
    }

    public function testGetPersonPositionRequiringDeltas()
    {
        $this->markTestSkipped();

        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_OTHER);

        $this->mockIdentifier(456);

        $this->sm->setService(
            'Lva\VariationPeople',
            m::mock()
            ->shouldReceive('getPersonPosition')
            ->with(123, 456, 5)
            ->andReturn('foo')
            ->getMock()
        );

        $this->assertEquals('foo', $this->sut->getPersonPosition(123, 5));
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

    public function testAlterAddOrEditForExceptionalOrganisation()
    {
        $this->markTestSkipped();

        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_SOLE_TRADER);

        $form = m::mock('Zend\Form\Form');

        $this->sm->setService(
            'Lva\People',
            m::mock()
            ->shouldReceive('lockPersonForm')
            ->with($form, OrganisationEntityService::ORG_TYPE_SOLE_TRADER)
            ->getMock()
        );

        $this->sut->alterAddOrEditFormForOrganisation($form, 123);
    }

    public function testAlterFormForNormalOrganisation()
    {
        $this->markTestSkipped();

        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_OTHER);

        $form = m::mock('Zend\Form\Form');
        $table = m::mock();

        $this->sm->setService(
            'Lva\People',
            m::mock()
            ->shouldReceive('lockOrganisationForm')
            ->never()
            ->getMock()
        );

        $this->sut->alterFormForOrganisation($form, $table, 123);
    }

    public function testAlterAddOrEditForNormalOrganisation()
    {
        $this->markTestSkipped();

        $this->mockOrg(123, OrganisationEntityService::ORG_TYPE_OTHER);

        $form = m::mock('Zend\Form\Form');

        $this->sm->setService(
            'Lva\People',
            m::mock()
            ->shouldReceive('lockPersonForm')
            ->never()
            ->getMock()
        );

        $this->sut->alterAddOrEditFormForOrganisation($form, 123);
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
