<?php

/**
 * Financial Evidence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

/**
 * Financial Evidence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialEvidenceController extends AbstractApplicationController
{
    public function indexAction()
    {
        return $this->render('financial_evidence');
    }
}
