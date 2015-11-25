<?php

/**
 * Index Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\RefData;

/**
 * Index Controller
 */
class IndexController extends AbstractController
{
    public function indexAction()
    {
        if ($this->isGranted(RefData::PERMISSION_SELFSERVE_DASHBOARD)) {
            // redir to the dashboard
            return $this->redirect()->toRoute('dashboard', [], ['code' => 303], false);
        }

        // redir to the search page
        return $this->redirect()->toRoute('search', [], ['code' => 303], false);
    }
}
