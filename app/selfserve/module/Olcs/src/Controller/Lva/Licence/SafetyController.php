<?php

/**
 * External Licence Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Zend\Form\Form;
use Common\Controller\Lva;
use Common\Controller\Lva\Traits\LicenceSafetyControllerTrait;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * External Licence Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyController extends Lva\AbstractSafetyController
{
    use LicenceSafetyControllerTrait,
        LicenceControllerTrait {
        LicenceSafetyControllerTrait::alterFormForLva as licenceSafetyAlterFormForLva;
        LicenceControllerTrait::alterFormForLva as licenceAlterFormForLva;
    }

    protected $lva = 'licence';
    protected $location = 'external';

    /**
     * This method allows both trait alterFormForLva methods to be called
     *
     * @param Form $form Form to alter
     * @param null $data Required for compatability with parent method signature
     *
     * @return void
     */
    protected function alterFormForLva(Form $form, $data = null)
    {
        $this->licenceAlterFormForLva($form);
        $this->licenceSafetyAlterFormForLva($form);
    }
}
