<?php

declare(strict_types=1);

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
class ApplicationGoodsVehiclesFiltersTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new ApplicationGoodsVehiclesFilters();
    }

    public function testGetForm(): void
    {
        $this->assertNull($this->sut->getForm());
    }
}
