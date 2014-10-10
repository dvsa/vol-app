<?php

/**
 * Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Common\View\Model\Section;

/**
 * Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesController extends AbstractApplicationController
{
    public function indexAction()
    {
        return new Section(
            array(
                'title' => 'Vehicles'
            )
        );
    }
}
