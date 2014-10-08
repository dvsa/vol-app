<?php

/**
 * FinancialEvidence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Section;
use Zend\Form\Form;

/**
 * FinancialEvidence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class FinancialEvidenceController extends AbstractApplicationController
{
    public function indexAction()
    {
        return new Section(
            array(
                'title' => 'Financial evidence'
            )
        );
    }
}
