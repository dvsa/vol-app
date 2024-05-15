<?php

namespace OlcsTest\Controller\Traits;

use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Data\DocumentSubCategory;

/**
 * @covers \Olcs\Controller\Traits\DocumentSearchTrait
 */
class DocumentSearchTraitTest extends MockeryTestCase
{
    protected $mockFormHelper;
    protected $docSubCategoryDataService;

    public const CAT_ID = 8001;

    /** @var Stub\StubDocumentSearchTrait */
    private $sut;

    public function setUp(): void
    {
        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->docSubCategoryDataService = m::mock(DocumentSubCategory::class)->makePartial();
        $this->sut = m::mock(\OlcsTest\Controller\Traits\Stub\StubDocumentSearchTrait::class, [
            $this->mockFormHelper,
            $this->docSubCategoryDataService
        ])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods(true);
    }

    public function testUpdateSelectValueOptions()
    {
        $options = [
            'exists1' => 'exists1_Val',
            'exists2' => 'exists2_Val',
            'exists3' => 'exists3_Val',
        ];

        $changeOptions =
            [
                'exists2' => 'exists2_NewVal',
                'new1' => 'new Val',
                'exists1' => null,
                'new2' => '',
                'new3' => null,
            ];

        $mockEl = m::mock(\Laminas\Form\Element\Select::class);
        $mockEl->shouldReceive('getValueOptions')->once()->andReturn($options);
        $mockEl->shouldReceive('setValueOptions')
            ->once()
            ->andReturnUsing(
                function ($arg) {
                    static::assertEquals(
                        [
                            'exists2' => 'exists2_NewVal',
                            'exists3' => 'exists3_Val',
                            'new1' => 'new Val',
                            'new2' => '',
                        ],
                        $arg
                    );

                    return $this;
                }
            );

        $this->sut->traitUpdateSelectValueOptions($mockEl, $changeOptions);
    }

    /**
     * @dataProvider dpTestMapDocumentFilters
     */
    public function testMapDocumentFilters($extra, $expect)
    {
        $mockRequest = m::mock(\Laminas\Http\Request::class);
        $mockRequest->shouldReceive('getQuery->toArray')->once()->andReturn(['query' => 'unit_Query']);

        $this->sut->shouldReceive('getRequest')->once()->andReturn($mockRequest);

        static::assertEquals(
            $expect,
            $this->sut->traitMapDocumentFilters($extra)
        );
    }

    public function dpTestMapDocumentFilters()
    {
        $def = [
            'sort' => 'issuedDate',
            'order' => 'DESC',
            'page' => 1,
            'limit' => 10,
            'showDocs' => FilterOptions::SHOW_ALL,
            'query' => 'unit_Query',
        ];

        return [
            [
                'extra' => [
                    'sort' => null,
                    'order' => '',
                    'page' => 0,
                    'showDocs' => '0',
                    'isExternal' => 'internal',
                    'newKey' => 'unit_NewKey',
                ],
                'expect' =>
                    [
                        'limit' => 10,
                        'isExternal' => 'N',
                        'newKey' => 'unit_NewKey',
                        'query' => 'unit_Query',
                    ],
            ],
            [
                'extra' => [
                    'order' => 'unit_newOrder',
                    'key1' => false,
                    'isExternal' => 'external',
                ],
                'expect' =>
                    [
                        'order' => 'unit_newOrder',
                        'key1' => false,
                        'isExternal' => 'Y',
                    ] + $def,
            ],
            [
                'extra' => [
                    'isExternal' => 'invalid',
                ],
                'expect' => $def,
            ],
        ];
    }

    public function testGetDocumentForm()
    {
        $expectCategory = 8777;
        $filters = [
            'category' => $expectCategory,
        ];

        $mockRequest = m::mock(\Laminas\Http\Request::class);

        $mockField = m::mock(\Laminas\Form\Element::class)->makePartial()
            ->shouldReceive('setValueOptions')->once()->with(m::hasKey(FilterOptions::SHOW_ALL))
            ->getMock();
        $mockFormatSelect = m::mock(\Laminas\Form\Element\Select::class);
        $mockFormatSelect->shouldReceive('setValueOptions')->with(['' => 'All', 'RTF' => 'RTF', 'D' => 'D'])->once();

        $mockForm = m::mock(\Laminas\Form\FormInterface::class)->makePartial()
            ->shouldReceive('setData')->once()->with($filters)
            ->shouldReceive('get')->once()->with('showDocs')->andReturn($mockField)
            ->shouldReceive('get')->once()->with('format')->andReturn($mockFormatSelect)
            ->getMock();

        $this->mockFormHelper
            ->shouldReceive('createForm')->once()->with('DocumentsHome', false)->andReturn($mockForm)
            ->shouldReceive('setFormActionFromRequest')->once()->with($mockForm, $mockRequest);

        $this->docSubCategoryDataService
            ->shouldReceive('setCategory')->once()->with($expectCategory)
            ->getMock();

        $mockDocumentListResponse = m::mock(\Common\Service\Cqrs\Response::class);
        $mockDocumentListResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockDocumentListResponse->shouldReceive('getResult')->with()->once()->andReturn(
            ['extra' => ['extensionList' => ['RTF', 'D']]]
        );

        //  call
        $this->sut
            ->shouldReceive('getRequest')->once()->andReturn($mockRequest)
            ->shouldReceive('handleQuery')->once()->andReturn($mockDocumentListResponse);

        $this->sut->traitGetDocumentForm($filters);
    }

    public function testGetDocumentsTable()
    {
        $mockDocumentListResponse = m::mock(\Common\Service\Cqrs\Response::class);
        $mockDocumentListResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockDocumentListResponse->shouldReceive('getResult')->with()->once()->andReturn('DOCUMENT_LIST');

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('getQuery')->with()->once()->andReturn('QUERY');
        $mockRequest->shouldReceive('getUri->getQuery')->with()->once()->andReturn(null);

        $filters = ['foo' => 'bar'];
        $this->sut->shouldReceive('getRequest')->with()->twice()->andReturn($mockRequest);
        $this->sut->shouldReceive('handleQuery')->once()->andReturn($mockDocumentListResponse);
        $this->sut->shouldReceive('getDocumentTableName')->with()->once()->andReturn('TABLE_NAME');
        $this->sut->shouldReceive('getTable')
            ->with('TABLE_NAME', 'DOCUMENT_LIST', ['foo' => 'bar', 'query' => 'QUERY'])
            ->once()
            ->andReturn('TABLE');

        $this->assertSame('TABLE', $this->sut->getDocumentsTable($filters));
    }

    public function testGetExtensionListNoValues()
    {
        $mockDocumentListResponse = m::mock(\Common\Service\Cqrs\Response::class);
        $mockDocumentListResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockDocumentListResponse->shouldReceive('getResult')->with()->once()->andReturn([]);

        $filters = ['foo' => 'bar'];
        $this->sut->shouldReceive('handleQuery')->once()->andReturn($mockDocumentListResponse);

        $this->assertSame([], $this->sut->getDocumentsExtensionList($filters));
    }
}
