<?php
/**
 * Transport manager note controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\TransportManager\Processing;

// @todo this is not really LVA, maybe just rename abstract / trait?
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;

use Mockery as m;

/**
 * Transport manager note controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransportManagerProcessingNoteControllerControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\TransportManager\Processing\TransportManagerProcessingNoteController');
    }

    /**
     * @dataProvider indexActionGetProvider
     * @param string $queryString
     */
    public function testIndexActionGet($tmId, $queryString, $expectedFilters)
    {

        $this->mockQueryString($queryString);

        $this->sut->shouldReceive('getRequest->getQuery')->andReturn($queryString);

        $this->sut->shouldReceive('params->fromRoute')->with('transportManager')->andReturn($tmId);

        $this->sut->shouldReceive('params->fromPost');

        $mockFilterForm = m::mock()
            ->shouldReceive('remove')->with('csrf')
            ->shouldReceive('setData')->with($expectedFilters)
            ->getMock();

        $this->sut->shouldReceive('getForm')->with('note-filter')->andReturn($mockFilterForm);
        $this->sut->shouldReceive('setTableFilters')->with($mockFilterForm);

        $notes = [
            [
                'id' => 22,
                'comment' => 'I\'m a note',
                'noteType' => [ 'id' => 'note_t_tm'],
            ],
            [
                'id' => 23,
                'comment' => 'Also a note',
                'noteType' => [ 'id' => 'note_t_tm'],
            ],

        ];
        $expectedTableData = [
            [
                'id' => 22,
                'comment' => 'I\'m a note',
                'noteType' => [ 'id' => 'note_t_tm'],
                'routePrefix' => 'transport-manager/processing',
            ],
            [
                'id' => 23,
                'comment' => 'Also a note',
                'noteType' => [ 'id' => 'note_t_tm'],
                'routePrefix' => 'transport-manager/processing',
            ],

        ];
        $this->mockService('Entity\Note', 'getNotesList')
            ->with($expectedFilters)
            ->andReturn($notes);

        $this->mockService('Table', 'buildTable')
            ->with('note', $expectedTableData, m::type('array'), false);

        $this->mockService('Script', 'loadFiles')->with(['forms/filter', 'table-actions']);

        $view = $this->sut->indexAction();
    }

    public function indexActionGetProvider()
    {
        return [
            'no query' => [
                3,
                null,
                [
                    'sort'             => 'priority',
                    'order'            => 'DESC',
                    'noteType'         => 'note_t_tm',
                    'transportManager' => 3,
                    'page'             => 1,
                    'limit'            => 10,
                ]

            ],
            'note type all' => [
                3,
                'noteType=', // 'All' is a blank value
                [
                    'sort'             => 'priority',
                    'order'            => 'DESC',
                    'transportManager' => 3,
                    'page'             => 1,
                    'limit'            => 10,
                ]
            ],
        ];
    }

    /**
     * @dataProvider indexActionPostRedirectProvider
     * @param int $tmId
     * @param int $id
     * @param string $action
     * @param array $redirectArgs
     */
    public function testIndexActionPostRedirects($tmId, $id, $action, $redirectArgs)
    {

        $this->sut->shouldReceive('params->fromRoute')->with('transportManager')->andReturn($tmId);
        $this->sut->shouldReceive('params->fromPost')->with('action')->andReturn($action);
        $this->sut->shouldReceive('params->fromPost')->with('id')->andReturn($id);

        $redirectResponse = m::mock();

        $this->sut
            ->shouldReceive('redirect->toRoute')
            ->withArgs($redirectArgs)
            ->andReturn($redirectResponse);

        $this->assertSame($redirectResponse, $this->sut->indexAction());
    }

    public function indexActionPostRedirectProvider()
    {
        return [
            [
                3,
                null,
                'Add',
                [
                    'transport-manager/processing/add-note',
                    [
                        'action'   => 'add',
                        'noteType' => 'note_t_tm',
                        'linkedId' => 3,
                    ],
                    [],
                    true
                ],

            ],
            [
                3,
                22,
                'Edit',
                [
                    'transport-manager/processing/modify-note',
                    [
                        'action'   => 'edit',
                        'id' => 22,
                    ],
                    [],
                    true
                ],

            ],
            [
                3,
                22,
                'Delete',
                [
                    'transport-manager/processing/modify-note',
                    [
                        'action'   => 'delete',
                        'id' => 22,
                    ],
                    [],
                    true
                ],

            ],
        ];
    }

    public function testAddAction()
    {
        $this->markTestIncomplete();
        $this->sut->addAction();
    }
}
