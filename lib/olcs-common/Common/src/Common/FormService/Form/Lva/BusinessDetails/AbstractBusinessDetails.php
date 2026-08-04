<?php

namespace Common\FormService\Form\Lva\BusinessDetails;

use Common\FormService\FormServiceInterface;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\FormHelperService;

/**
 * Abstract Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractBusinessDetails
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    public function getForm($orgType, $hasInforceLicences, bool $hasOrganisationSubmittedLicenceApplication, bool $isLicenseApplicationPsv = false)
    {
        $form = $this->formHelper->createForm('Lva\BusinessDetails');

        $params = [
            'orgType' => $orgType,
            'hasInforceLicences' => $hasInforceLicences,
            'hasOrganisationSubmittedLicenceApplication' => $hasOrganisationSubmittedLicenceApplication,
            'isLicenseApplicationPsv' => $isLicenseApplicationPsv,
        ];

        $this->alterForm($form, $params);

        return $form;
    }

    /**
     * @param (bool|mixed)[] $params
     *
     * @psalm-param array{orgType: mixed, hasInforceLicences: mixed, hasOrganisationSubmittedLicenceApplication: bool} $params
     *
     * @return void
     */
    protected function alterForm($form, $params)
    {
        switch ($params['orgType']) {
            case RefData::ORG_TYPE_REGISTERED_COMPANY:
            case RefData::ORG_TYPE_LLP:
                // no-op; the full form is fine
                break;
            case RefData::ORG_TYPE_SOLE_TRADER:
                $this->alterFormForNonRegisteredCompany($form);
                $this->formHelper->remove($form, 'data->name');
                break;
            case RefData::ORG_TYPE_PARTNERSHIP:
                $this->alterFormForNonRegisteredCompany($form);
                $this->appendToLabel($form->get('data')->get('name'), '.partnership');
                break;
            case RefData::ORG_TYPE_OTHER:
                $this->alterFormForNonRegisteredCompany($form);
                $this->formHelper->remove($form, 'data->tradingNames');
                $this->appendToLabel($form->get('data')->get('name'), '.other');
                break;
        }

        if ($params['isLicenseApplicationPsv'] && $form->has('table')) {
            $this->formHelper->remove($form, 'table');
        }
    }

    protected function appendToLabel($element, $append): void
    {
        $this->formHelper->alterElementLabel($element, $append, FormHelperService::ALTER_LABEL_APPEND);
    }

    /**
     * Make generic form alterations for non limited (or LLP) companies
     *
     * @param \Laminas\Form\Form $form
     */
    protected function alterFormForNonRegisteredCompany($form): void
    {
        $this->formHelper->remove($form, 'table')
            ->remove($form, 'data->companyNumber')
            ->remove($form, 'registeredAddress');
    }
}
