<?php

/**
 * Internal Licencing Operating Centres Controller
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
 * Internal Licencing Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController
{
    use LicenceControllerTrait,
        LicenceOperatingCentresControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';

    public function indexAction()
    {
        // we can't traitify this due to the parent reference...
        $this->addVariationInfoMessage();
        return parent::indexAction();
    }

    /**
     * Alter the form
     *
     * @TODO should live in the licence trait, but calls parent... so needs refactoring
     * Therefore currently duped across internal and external
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    public function alterForm(Form $form)
    {
        $form = parent::alterForm($form);
        return $this->commonAlterForm($form);
    }
}
