<?php

/**
 * Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Zend\Form\Form;
use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Controller\Lva\Traits\LicenceOperatingCentresControllerTrait;

/**
 * Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController
{
    use LicenceControllerTrait,
        LicenceOperatingCentresControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';

    /**
     * Alter the form
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    /*
    public function alterForm(Form $form)
    {
        return $form;
    }

    protected function getIdentifier()
    {
        return $this->getLicenceId();
    }
     */
}
