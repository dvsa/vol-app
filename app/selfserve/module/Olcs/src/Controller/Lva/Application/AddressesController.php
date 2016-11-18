<?php

/**
 * External Application Addresses Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

use Zend\Form\Form;

/**
 * External Application Addresses Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddressesController extends Lva\AbstractAddressesController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';

    /**
     * Alter form for LVA
     *
     * @param Form  $form form
     * @param array $data data
     *
     * @return void
     */
    protected function alterFormForLva(Form $form, $data = null)
    {
        $this->getServiceLocator()->get('Helper\Form')->remove($form, 'consultant');
        $this->getServiceLocator()->get('Helper\Form')->remove($form, 'consultantContact');
        $this->getServiceLocator()->get('Helper\Form')->remove($form, 'consultantAddress');
    }
}
