<?php

namespace OlcsTest\Controller\Lva\Application;

use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use Mockery as m;
use Common\Service\Entity\OrganisationEntityService;

/**
 * Test People Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class PeopleControllerTest extends AbstractLvaControllerTestCase
{
    private $adapter;

    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Application\PeopleController');

        $this->mockService('Helper\FlashMessenger', 'addSuccessMessage');

        $this->adapter = m::mock('\Common\Controller\Lva\Interfaces\AdapterInterface');

        $this->sut->setAdapter($this->adapter);
    }

    /**
     * Test index action with edit and set operator name for sole trader
     *
     * @group peopleController
     */
    public function testIndexActionWithEditAndSetOperatorNameForSoleTrader()
    {
        $this->markTestSkipped();

        $person = [
            'birthPlace' => '2014-01-01',
            'otherName' => 'other',
            'birthDate' => '1973-01-01',
            'familyName' => 'foo',
            'forename' => 'bar',
            'id' => 1,
            'title' => 'Mr',
            'version' => 1
        ];
        $form = $this->createMockForm('Lva\SoleTrader');

        $form->shouldReceive('setData')
            ->with(['data' => $person])
            ->andReturn($form)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn(['data' => $person]);

        $this->sut
            ->shouldReceive('getCurrentOrganisationId')
            ->andReturn(1)
            ->shouldReceive('completeSection')
            ->with('people')
            ->shouldReceive('commonPostSave')
            ->with('people')
            ->shouldReceive('getIdentifier')
            ->andReturn(123);

        $this->mockEntity('Organisation', 'getType')
            ->with(1)
            ->andReturn(
                [
                    'type' => ['id' => OrganisationEntityService::ORG_TYPE_SOLE_TRADER],
                    'id' => 1,
                    'version' => 1
                ]
            )
            ->shouldReceive('save')
            ->with(
                [
                    'name' => 'bar foo',
                    'id' => 1,
                    'version' => 1
                 ]
            );

        $this->mockEntity('Person', 'getFirstForOrganisation')
            ->with(1)
            ->andReturn($person)
            ->shouldReceive('save');

        $this->setPost(['data' => $person]);

        $this->adapter->shouldReceive('alterAddOrEditFormForOrganisation')
            ->with($form, 1)
            ->shouldReceive('addMessages')
            ->with(1, 123)
            ->shouldReceive('save');

        $this->sut->indexAction();
    }

    /**
     * Test add action with edit and set operator name for partnership
     *
     * @group peopleController
     * @dataProvider personsProvider
     */
    public function testAddActionWithEditAndSetOperatorNameForPartnership($persons, $expectedOperatorName)
    {
        $this->markTestSkipped();

        $post = [
            'data' => [
                'birthPlace' => '2014-01-01',
                'otherName' => 'other',
                'birthDate' => [
                    'day' => '01',
                    'month' => '01',
                    'year' => '2014'
                ],
                'familyName' => 'foo',
                'forename' => 'bar',
                'title' => 'Mr',
             ]
        ];

        $this->sut
            ->shouldReceive('getCurrentOrganisationId')
            ->andReturn(1)
            ->shouldReceive('getIdentifierIndex')
            ->andReturn('id')
            ->shouldReceive('getIdentifier')
            ->andReturn(1)
            ->shouldReceive('isButtonPressed')
            ->with('addAnother')
            ->andReturn(false)
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRouteAjax')
                ->with(null, ['id' => 1])
                ->getMock()
            )
            ->shouldReceive('params')
            ->shouldReceive('commonPostSave')
            ->with('people');

        $this->mockEntity('Organisation', 'getType')
            ->with(1)
            ->andReturn(
                [
                    'type' => ['id' => OrganisationEntityService::ORG_TYPE_PARTNERSHIP],
                    'id' => 1,
                    'version' => 1
                ]
            );

        $this->setPost($post);

        $form = $this->createMockForm('Lva\Person');

        $form->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($post);

        $this->formHelper
            ->shouldReceive('remove')
            ->with($form, 'data->position');

        $this->mockEntity('Person', 'save')
            ->shouldReceive('getAllForOrganisation')
            ->with(1, 'all')
            ->andReturn($persons);

        $saveDetails = [
            'name' => $expectedOperatorName,
            'id' => 1,
            'version' => 1
        ];

        $this->mockEntity('Organisation', 'save')
            ->with($saveDetails);

        $this->adapter->shouldReceive('canModify')
            ->with(1)
            ->andReturn(true)
            ->shouldReceive('alterAddOrEditFormForOrganisation')
            ->with($form, 1)
            ->shouldReceive('save');

        $this->sut->addAction();
    }

    /**
     * Persons provider
     */
    public function personsProvider()
    {
        return [
            [['Results' => [['person' => ['forename' => 'forename', 'familyName' => 'familyName']]]],
                'forename familyName'],
            [['Results' => []], ''],
            [['Results' => [
                ['person' => ['forename' => 'forename', 'familyName' => 'familyName']],
                ['person' => ['forename' => 'forename1', 'familyName' => 'familyName1']],
            ]], 'forename familyName & forename1 familyName1'],
            [['Results' => [
                ['person' => ['forename' => 'forename', 'familyName' => 'familyName']],
                ['person' => ['forename' => 'forename1', 'familyName' => 'familyName1']],
                ['person' => ['forename' => 'forename2', 'familyName' => 'familyName2']],
            ]], 'forename familyName & Partners'],
        ];
    }
}
