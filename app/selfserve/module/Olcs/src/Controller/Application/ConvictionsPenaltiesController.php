<?php

/**
 * ConvictionsPenalties Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Common\View\Model\Section;

/**
 * ConvictionsPenalties Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ConvictionsPenaltiesController extends AbstractApplicationController
{
    public function indexAction()
    {
        return new Section(
            array(
                'title' => 'Convictions & penalties'
            )
        );
    }
}
