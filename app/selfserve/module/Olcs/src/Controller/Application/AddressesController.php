<?php

/**
 * Addresses Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Common\View\Model\Section;

/**
 * Addresses Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class AddressesController extends AbstractApplicationController
{
    public function indexAction()
    {
        return new Section(
            array(
                'title' => 'Addresses'
            )
        );
    }
}
