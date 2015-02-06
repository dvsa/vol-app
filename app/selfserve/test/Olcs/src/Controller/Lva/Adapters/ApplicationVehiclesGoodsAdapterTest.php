<?php

/**
 * External Application Goods Vehicle Adapter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\ApplicationVehiclesGoodsAdapter;

/**
 * External Application Goods Vehicle Adapter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationVehiclesGoodsAdapterTest extends MockeryTestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new ApplicationVehiclesGoodsAdapter();
    }

    public function testShowFilters()
    {
        $this->assertFalse($this->sut->showFilters());
    }
}
