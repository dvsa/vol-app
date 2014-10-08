<?php

/**
 * Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Section;
use Zend\Form\Form;

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
