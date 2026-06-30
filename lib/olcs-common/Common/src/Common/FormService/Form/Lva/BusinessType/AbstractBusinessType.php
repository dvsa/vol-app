<?php

namespace Common\FormService\Form\Lva\BusinessType;

use Common\FormService\Form\Lva\AbstractLvaFormService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Business Type Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractBusinessType extends AbstractLvaFormService
{
    protected $lva;

    protected GuidanceHelperService $guidanceHelper;

    public function getForm($inForceLicences, bool $hasOrganisationSubmittedLicenceApplication)
    {
        $form = $this->formHelper->createForm('Lva\BusinessType');

        $params = [
            'inForceLicences' => $inForceLicences,
            'hasOrganisationSubmittedLicenceApplication' => $hasOrganisationSubmittedLicenceApplication,
        ];

        $this->alterForm($form, $params);

        return $form;
    }

    /**
     * @param (bool|mixed)[] $params
     *
     * @psalm-param array{inForceLicences: mixed, hasOrganisationSubmittedLicenceApplication: bool} $params
     *
     * @return void
     */
    protected function alterForm(Form $form, $params)
    {
        // Noop
    }

    protected function lockForm(Form $form, $removeStandardActions = true): void
    {
        $element = $form->get('data')->get('type');

        $this->formHelper->lockElement($element, 'business-type.locked');

        $this->formHelper->disableElement($form, 'data->type');

        $this->guidanceHelper->append('business-type.locked.message');

        if ($removeStandardActions) {
            $this->removeStandardFormActions($form);
            $this->addBackToOverviewLink($form, $this->lva);
        }
    }
}
