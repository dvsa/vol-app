<?php

namespace Olcs\FormService\Form\Lva\OperatingCentres;

use Common\Form\Elements\Validators\TableRequiredValidator;
use Common\FormService\Form\Lva\OperatingCentres\AbstractOperatingCentres;
use Common\FormService\FormServiceManager;
use Common\Service\Table\TableFactory;
use Laminas\Form\Form;
use Common\Service\Helper\FormHelperService;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationOperatingCentres extends AbstractOperatingCentres
{
    use ButtonsAlterations;

    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService, protected $tableBuilder, protected FormServiceManager $formServiceLocator)
    {
    }

    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params paramas
     *
     * @return void
     */
    #[\Override]
    protected function alterForm(Form $form, array $params)
    {
        $inputFilter = $form->getInputFilter();
        $tableInputFilter = $inputFilter->get('table');
        $rowsInput = $tableInputFilter->get('rows');
        $tableRequiredValidator = new TableRequiredValidator();
        $rowsInput->getValidatorChain()->attach($tableRequiredValidator);

        $this->formServiceLocator->get('lva-application')->alterForm($form);

        parent::alterForm($form, $params);
        $this->alterButtons($form);

        if ($form->has('table')) {
            $table = $form->get('table')->get('table')->getTable();
            $table->removeColumn('noOfComplaints');
        }

        if ($form->get('data')->has('totCommunityLicencesFieldset')) {
            $this->formHelper->alterElementLabel(
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
