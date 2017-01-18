<?php

namespace OlcsTest\Controller\Traits;

use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Data\DocumentSubCategory as DocumentSubCategoryDS;

/**
 * @covers \Olcs\Controller\Traits\DocumentSearchTrait
 */
class DocumentSearchTraitTest extends MockeryTestCase
{
    const CAT_ID = 8001;

    /** @var Stub\StubDocumentSearchTrait */
    private $sut;

    public function setUp()
    {
        $this->sut = m::mock(\OlcsTest\Controller\Traits\Stub\StubDocumentSearchTrait::class)
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

        $mockEl = m::mock(\Zend\Form\Element\Select::class);
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
        $mockRequest = m::mock(\Zend\Http\Request::class);
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

        $mockRequest = m::mock(\Zend\Http\Request::class);

        $mockField = m::mock(\Zend\Form\Element::class)->makePartial()
            ->shouldReceive('setValueOptions')->once()->with(m::hasKey(FilterOptions::SHOW_ALL))
            ->getMock();

        $mockForm = m::mock(\Zend\Form\FormInterface::class)->makePartial()
            ->shouldReceive('remove')->once()->with('csrf')
            ->shouldReceive('setData')->once()->with($filters)
            ->shouldReceive('get')->once()->with('showDocs')->andReturn($mockField)
            ->getMock();

        $mockFormHelper = m::mock(\Common\Service\Helper\FormHelperService::class)->makePartial()
            ->shouldReceive('setFormActionFromRequest')->once()->with($mockForm, $mockRequest)
            ->getMock();

        $mockDocSubCatDs = m::mock(DocumentSubCategoryDS::class)
            ->shouldReceive('setCategory')->once()->with($expectCategory)
            ->getMock();

        $mockSm = m::mock(\Zend\Di\ServiceLocatorInterface::class)
            ->shouldReceive('get')->with('Helper\Form')->once()->andReturn($mockFormHelper)
            ->shouldReceive('get')->with(DocumentSubCategoryDS::class)->once()->andReturn($mockDocSubCatDs)
            ->getMock();

        //  call
        $this->sut
            ->shouldReceive('getRequest')->once()->andReturn($mockRequest)
            ->shouldReceive('getServiceLocator')->once()->andReturn($mockSm)
            ->shouldReceive('getForm')->once()->andReturn($mockForm);

        $this->sut->traitGetDocumentForm($filters);
    }
}
