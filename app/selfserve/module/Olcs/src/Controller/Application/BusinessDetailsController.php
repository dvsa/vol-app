<?php

/**
 * Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Section;
use Zend\Form\Form;

/**
 * Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BusinessDetailsController extends AbstractApplicationController
{
    public function indexAction()
    {
        return new Section(
            array(
                'title' => 'Business details'
            )
        );
    }
}
