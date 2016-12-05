<?php

namespace OlcsTest\Controller\Traits;

use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * @covers \Olcs\Controller\Traits\DocumentSearchTrait
 */
class DocumentSearchTraitTest extends MockeryTestCase
{
    /** @var \OlcsTest\Controller\Traits\Stub\StubDocumentSearchTrait */
    private $sut;

    public function setUp()
    {
        $this->sut = new \OlcsTest\Controller\Traits\Stub\StubDocumentSearchTrait();
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

        $this->sut->request = $mockRequest;

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
            'showDocs' => FilterOptions::SHOW_SELF_ONLY,
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
                        'query' => 'unit_Query'
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
}
