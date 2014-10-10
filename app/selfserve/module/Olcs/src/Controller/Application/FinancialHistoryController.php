<?php

/**
 * FinancialHistory Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Common\View\Model\Section;

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
