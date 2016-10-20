<?php

namespace Olcs\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\Lva\OperatingCentres\LicenceOperatingCentres as CommonLicenceOperatingCentres;
use Zend\Form\Form;

/**
 * Licence Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceOperatingCentres extends CommonLicenceOperatingCentres
{
    protected $mainTableConfigName = 'lva-licence-operating-centres';

    private $lockElements = [
        'totAuthVehicles',
        'totAuthTrailers'
    ];

    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return void
     */
    protected function alterForm(Form $form, array $params)
    {
        parent::alterForm($form, $params);

        $this->getFormHelper()->disableElements($form->get('data'));

        if ($form->has('dataTrafficArea')) {
            $form->get('dataTrafficArea')->remove('enforcementArea');
        }

        foreach ($this->lockElements as $lockElement) {
            if ($form->get('data')->has($lockElement)) {
                $this->getFormHelper()->lockElement(
                    $form->get('data')->get($lockElement),
                    'operating-centres-licence-locked'
                );
            }
        }

        $this->removeStandardFormActions($form);
    }
}
