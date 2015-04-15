<?php

/**
 * External Application Operating Centre Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Adapters\ApplicationOperatingCentreAdapter as CommonApplicationOperatingCentreAdapter;
use Common\Service\Helper\FormHelperService;

/**
 * Application Operating Centre Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationOperatingCentreAdapter extends CommonApplicationOperatingCentreAdapter
{
    /**
     * Alter the form
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    public function alterForm(Form $form)
    {
        $form = parent::alterForm($form);

        $table = $form->get('table')->get('table')->getTable();
        $table->removeColumn('noOfComplaints');

        if ($form->get('data')->has('totCommunityLicences')) {
            $this->getServiceLocator()->get('Helper\Form')->alterElementLabel(
                $form->get('data')->get('totCommunityLicences'),
                '-external-app',
                FormHelperService::ALTER_LABEL_APPEND
            );
        }

        if ($form->has('dataTrafficArea')) {
            $form->get('dataTrafficArea')->remove('enforcementArea');
        }

        return $form;
    }

    /**
     * Alter action form for PSV licences
     *
     * @param \Zend\Form\Form $form
     */
    protected function alterActionFormForPsv(Form $form)
    {
        // if PSV restricted licence, then add validtor max vehicles is two
        $typeOfLicence = $this->getTypeOfLicenceData();
        if ($typeOfLicence['licenceType'] === \Common\Service\Entity\LicenceEntityService::LICENCE_TYPE_RESTRICTED) {
            $formHelper = $this->getServiceLocator()->get('Helper\Form');
            $newValidator = new \Zend\Validator\LessThan(
                ['max' => 3, 'message' => 'OperatingCentreVehicleAuthorisationValidator.too-high-psv-r']
            );

            $formHelper->attachValidator($form, 'data->noOfVehiclesRequired', $newValidator);
        }
    }
}
