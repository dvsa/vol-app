<?php

/**
 * ConvictionsPenalties Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Section;
use Zend\Form\Form;

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
