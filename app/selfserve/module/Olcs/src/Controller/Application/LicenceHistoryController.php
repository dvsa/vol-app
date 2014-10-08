<?php

/**
 * LicenceHistory Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Section;
use Zend\Form\Form;

/**
 * LicenceHistory Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceHistoryController extends AbstractApplicationController
{
    public function indexAction()
    {
        return new Section(
            array(
                'title' => 'Licence history'
            )
        );
    }
}
