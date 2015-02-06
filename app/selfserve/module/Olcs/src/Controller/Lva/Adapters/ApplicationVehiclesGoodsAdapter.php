<?php

/**
 * Application Vehicles Goods Adapter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\ApplicationVehiclesGoodsAdapter as CommonApplicationVehicleGoodsAdapter;

/**
 * Application Vehicles Goods Adapter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationVehiclesGoodsAdapter extends CommonApplicationVehicleGoodsAdapter
{
    public function showFilters()
    {
        return false;
    }
}
