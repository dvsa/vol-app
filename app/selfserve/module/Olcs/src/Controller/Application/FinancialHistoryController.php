<?php

/**
 * FinancialHistory Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Section;
use Zend\Form\Form;

/**
 * FinancialHistory Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class FinancialHistoryController extends AbstractApplicationController
{
    public function indexAction()
    {
        return new Section(
            array(
                'title' => 'Financial history'
            )
        );
    }
}
