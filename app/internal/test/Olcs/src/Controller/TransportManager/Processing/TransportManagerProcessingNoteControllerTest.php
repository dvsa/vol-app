<?php
/**
 * Transport manager note controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\TransportManager\Processing;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\Controller\Traits\ControllerTestTrait;
use OlcsTest\Bootstrap;

/**
 * Transport manager note controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransportManagerProcessingNoteControllerTest extends MockeryTestCase
{
    use ControllerTestTrait;

    protected function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }

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

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $this->sut->indexAction());
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

    public function testAddActionGet()
    {
        $tmId = 3;
        $this->sut->shouldReceive('params->fromRoute')->with('transportManager')->andReturn($tmId);

        $mockForm = $this->mockNoteForm();

        $mockForm->shouldReceive('setData')->with(
            ['transportManager' => $tmId, 'noteType' => 'note_t_tm']
        );

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $this->sut->addAction());
    }

    public function testAddActionPostSuccess()
    {
        $tmId = 3;
        $this->sut->shouldReceive('params->fromRoute')->with('transportManager')->andReturn($tmId);

        $mockForm = $this->mockNoteForm();

        $postData = [
            'id' => '',
            'transportManager' => $tmId,
            'noteType' => 'note_t_tm',
            'main' => [
                'comment' => 'foo',
                'priority' => 'Y'
            ],
        ];
        $this->setPost($postData);

        $mockForm
            ->shouldReceive('setData')
                ->with($postData)
                ->andReturnSelf()
            ->shouldReceive('getData')
                ->andReturn($postData)
            ->shouldReceive('isValid')
                ->andReturn(true)
            ->getMock();

        $this->sut->shouldReceive('getLoggedInUser')->andReturn(9);

        $this->mockService('Helper\Rest', 'makeRestCall')
            ->with(
                'Note',
                'POST',
                [
                    'id'               => '',
                    'transportManager' => 3,
                    'noteType'         => 'note_t_tm',
                    'comment'          => 'foo',
                    'priority'         => 'Y',
                    'createdBy'        => 9,
                    'lastModifiedBy'   => 9,
                    // this gets left in by array_merge but is ignored on save
                    'main' => ['comment' => 'foo', 'priority' => 'Y'],
                ],
                null
            )
            ->andReturn(['id' => 23 ]);

        $this->assertRedirectToIndex($tmId);

        $this->sut->addAction();
    }

    public function testAddActionPostFail()
    {
        $tmId = 3;
        $this->sut->shouldReceive('params->fromRoute')->with('transportManager')->andReturn($tmId);

        $mockForm = $this->mockNoteForm();

        $postData = [];
        $this->setPost($postData);

        $mockForm
            ->shouldReceive('setData')
                ->with($postData)
                ->andReturnSelf()
            ->shouldReceive('getData')
                ->andReturn($postData)
            ->shouldReceive('isValid')
                ->andReturn(false)
            ->getMock();

        $this->mockService('Helper\Rest', 'makeRestCall')
            ->with(m::any())
            ->andReturn([]);

        $this->sut->shouldReceive('redirect')->never();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $this->sut->addAction());
    }

    public function testEditActionGet()
    {
        $tmId = 3;
        $id   = 24;

        $this->sut->shouldReceive('params->fromRoute')->with('transportManager')->andReturn($tmId);
        $this->sut->shouldReceive('params->fromRoute')->with('id')->andReturn($id);

        $mockForm = $this->mockNoteForm();

        $this->mockService('Entity\Note', 'getNote')->with($id)->andReturn(
            [
                'id'       => $id,
                'version'  => 1,
                'comment'  => 'this is a note',
                'priority' => 'N',
            ]
        );

        $mockForm->shouldReceive('setData')->with(
            [
                'id'       => $id,
                'version'  => 1,
                'main' => [
                    'comment'  => 'this is a note',
                    'priority' => 'N',
                ]
            ]
        );

        $this->mockService('Helper\Form', 'disableElement')
            ->with($mockForm, 'main->comment');

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $this->sut->editAction());
    }

    public function testEditActionPostSuccess()
    {
        $tmId = 3;
        $id   = 24;

        $this->sut->shouldReceive('params->fromRoute')->with('transportManager')->andReturn($tmId);
        $this->sut->shouldReceive('params->fromRoute')->with('id')->andReturn($id);

        $this->mockService('Entity\Note', 'getNote')->with($id);

        $mockForm = $this->mockNoteForm();

        $postData = [
            'id'               => 24,
            'version'          => 2,
            'transportManager' => '',
            'noteType'         => '',
            'main'             => [
                'priority' => 'Y'
                // no 'comment', as the field is disabled
            ],
        ];
        $this->setPost($postData);

        $mockForm
            ->shouldReceive('setData')
                ->with($postData)
                ->andReturnSelf()
            ->shouldReceive('getData')
                ->andReturn($postData)
            ->shouldReceive('isValid')
                ->andReturn(true)
            ->getMock();

        $this->mockService('Helper\Form', 'disableElement')
            ->with($mockForm, 'main->comment');

        $this->sut->shouldReceive('getLoggedInUser')->andReturn(9);

        $this->mockService('Helper\Rest', 'makeRestCall')
            ->with(
                'Note',
                'PUT',
                [
                    'id'               => 24,
                    'version'          => 2,
                    'priority'         => 'Y',
                    'lastModifiedBy'   => 9,
                    'main' => ['priority' => 'Y'], // this gets left in by array_merge but is ignored on save
                ],
                null
            )
            ->andReturn([]); // we get empty array back on success(!)

        $this->assertRedirectToIndex($tmId);

        $this->sut->editAction();
    }

    protected function mockNoteForm()
    {
        $mockForm =  m::mock()
            ->shouldReceive('hasAttribute')
                ->with('action')
                ->andReturn(true)
            ->shouldReceive('getFieldsets')
                ->andReturn([])
            ->getMock();

        $this->mockService('Helper\Form', 'createForm')
            ->with('Note')
            ->andReturn($mockForm);

        return $mockForm;
    }

    protected function assertRedirectToIndex($tmId)
    {
        $this->sut
            ->shouldReceive('redirect->toRouteAjax')
            ->once()
            ->with(
                'transport-manager/processing/notes',
                ['transportManager' => $tmId],
                [],
                false
            );
    }
}
