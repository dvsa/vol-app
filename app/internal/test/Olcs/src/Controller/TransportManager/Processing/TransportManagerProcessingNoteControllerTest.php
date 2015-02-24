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
     * @dataProvider indexGetProvider
     * @param string $queryString
     */
    public function testGetIndexAction($tmId, $queryString, $expectedFilters)
    {

        $this->mockQueryString($queryString);

        $this->sut->shouldReceive('getRequest->getQuery')->andReturn($queryString);

        $this->sut->shouldReceive('params->fromRoute')->with('transportManager')->andReturn($tmId);

        $this->sut->shouldReceive('getFromPost');

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

    public function indexGetProvider()
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

    protected function mockQueryString($queryString)
    {
        $params = new \Zend\Stdlib\Parameters();
        $params->fromString($queryString);
        $this->sut->getRequest()->setQuery($params);
    }
}
