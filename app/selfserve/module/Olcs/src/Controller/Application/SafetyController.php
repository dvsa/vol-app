<?php

/**
 * Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Section;
use Zend\Form\Form;

/**
 * Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class SafetyController extends AbstractApplicationController
{
    public function indexAction()
    {
        return new Section(
            array(
                'title' => 'Safety'
            )
        );
    }
}
