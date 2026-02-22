<?php

namespace Olcs\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\Lva\OperatingCentres\VariationOperatingCentres as CommonVariationOperatingCentres;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOperatingCentres extends CommonVariationOperatingCentres
{
    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;
    protected $tableBuilder;
    protected FormServiceManager $formServiceLocator;
    protected TranslationHelperService $translator;

    public function __construct(
        FormHelperService $formHelper,
        AuthorizationService $authService,
        $tableBuilder,
        FormServiceManager $formServiceLocator,
        TranslationHelperService $translator
    ) {
        parent::__construct($formHelper, $authService, $tableBuilder, $formServiceLocator, $translator);
    }

    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return void
     */
    #[\Override]
    protected function alterForm(Form $form, array $params)
    {
        parent::alterForm($form, $params);

        if ($form->has('table')) {
            $table = $form->get('table')->get('table')->getTable();
            $table->removeColumn('noOfComplaints');
        }

        if ($form->has('dataTrafficArea')) {
            $form->get('dataTrafficArea')->remove('enforcementArea');
        }
        $this->formHelper->remove($form, 'form-actions->cancel');
    }

    #[\Override]
    protected function alterFormForPsvLicences(Form $form, array $params)
    {
        parent::alterFormForPsvLicences($form, $params);
        $this->alterFormWithTranslationKey($form, 'community-licence-changes-contact-office.psv');
    }

    /**
     * @inheritDoc
     */
    protected function alterFormForGoodsLicences(Form $form, array $params): void
    {
        parent::alterFormForGoodsLicences($form, $params);
        $this->alterFormWithTranslationKey($form, 'community-licence-changes-contact-office');
    }

    /**
     * Apply a padlock to the totCommunityLicences field using the specified translation key as a tooltip
     *
     * @param string $translationKey
     * @return void
     */
    protected function alterFormWithTranslationKey(Form $form, $translationKey)
    {
        if ($form->get('data')->has('totCommunityLicencesFieldset')) {
            $this->formHelper->lockElement(
                $form->get('data')->get('totCommunityLicencesFieldset')->get('totCommunityLicences'),
                $translationKey
            );
        }
    }
}
