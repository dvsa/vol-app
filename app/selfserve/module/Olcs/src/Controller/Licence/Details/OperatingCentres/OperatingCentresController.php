<?php

/**
 * Operating Centre Controller
 *
 * External - Licence section
 */
namespace Olcs\Controller\Licence\Details\OperatingCentres;

use Olcs\Controller\Licence\Details\AbstractLicenceDetailsController;

/**
 * Operating Centre Controller
 */
class OperatingCentresController extends AbstractLicenceDetailsController
{
    protected $navigationItem = 'licence_operating_centres';

    public function indexAction()
    {
        return $this->redirect()->toRoute('licence/operating_centres/authorisation', array(), array(), true);
    }
}
