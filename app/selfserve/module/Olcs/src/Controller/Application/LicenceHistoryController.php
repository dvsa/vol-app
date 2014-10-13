<?php

/**
 * Licence History Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

/**
 * Licence History Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceHistoryController extends AbstractApplicationController
{
    public function indexAction()
    {
        return $this->render('licence_history');
    }
}
