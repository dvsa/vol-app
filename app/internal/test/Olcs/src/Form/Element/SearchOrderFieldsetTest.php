<?php

declare(strict_types=1);

namespace OlcsTest\Form\Element;

use Common\Service\Data\Search\Search as SearchDataService;
use Olcs\Form\Element\SearchOrderFieldset;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class SearchOrderFieldsetTest extends TestCase
{
    public function testSearchAwareTraitByProxy()
    {
        $service = m::mock(SearchDataService::class);

        $sut = new SearchOrderFieldset();

        $this->assertSame($service, $sut->setSearchService($service)->getSearchService());
    }

    public function testInit()
    {
        $orderOptions = [
            [
                'field' => 'field_1',
                'field_label' => 'label 1',
                'order' => 'desc'
            ],
            [
                'field' => 'field_2',
                'field_label' => 'label 2',
                'order' => 'asc'
            ]
        ];

        $index = 'index-name';
        $options = ['index' => $index];

        $service = m::mock(SearchDataService::class);
        $service->expects('setIndex')->with($index);
        $service->shouldReceive('getOrderOptions')->withNoArgs()->once()->andReturn($orderOptions);

        $sut = new SearchOrderFieldset(null, $options);
        $sut->setSearchService($service);
        $sut->init();

        $order = $sut->get('order');
        $this->assertInstanceOf(\Laminas\Form\Element\Select::class, $order);
        $this->assertSame(
            [
                'field_1-desc' => 'label 1',
                'field_2-asc' => 'label 2',
            ],
            $order->getValueOptions()
        );
        $this->assertNotEmpty($order->getEmptyOption());
    }
}
