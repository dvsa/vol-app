<?php

namespace Olcs\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\Lva\OperatingCentres\AbstractOperatingCentres;
use Laminas\Form\Form;
use Common\Service\Helper\FormHelperService;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationOperatingCentres extends AbstractOperatingCentres
{
    use ButtonsAlterations;

    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params paramas
     *
     * @return void
     */
    protected function alterForm(Form $form, array $params)
    {
        $this->getFormServiceLocator()->get('lva-application')->alterForm($form);

        parent::alterForm($form, $params);
        $this->alterButtons($form);

        if ($form->has('table')) {
            $table = $form->get('table')->get('table')->getTable();
            $table->removeColumn('noOfComplaints');
        }

        if ($form->get('data')->has('totCommunityLicencesFieldset')) {
            $this->getFormHelper()->alterElementLabel(
                $form->get('data')->get('totCommunityLicencesFieldset')->get('totCommunityLicences'),
                '-external-app',
                FormHelperService::ALTER_LABEL_APPEND
            );
        }

        if ($form->has('dataTrafficArea')) {
            $form->get('dataTrafficArea')->remove('enforcementArea');
        }
    }
}
