<?php

/**
 * Application Goods Vehicles Filters Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva;

use PHPUnit_Framework_TestCase;
use Olcs\FormService\Form\Lva\ApplicationGoodsVehiclesFilters;

/**
 * Application Goods Vehicles Filters Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationGoodsVehiclesFiltersTest extends PHPUnit_Framework_TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new ApplicationGoodsVehiclesFilters();
    }

    public function testGetForm()
    {
        $this->assertNull($this->sut->getForm());
    }
}
