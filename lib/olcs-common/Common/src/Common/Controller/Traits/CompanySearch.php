<?php

namespace Common\Controller\Traits;

use Common\Form\Form;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\FormHelperService as FormHelper;
use Dvsa\Olcs\Transfer\Query\CompaniesHouse\ByNumber;

trait CompanySearch
{
    public static $companyNameLength = 8;

    public function populateCompanyDetails(FormHelper $formHelper, $form, $detailsFieldset, $addressFieldset, $companyNumber): Form
    {
        try {
            $response = $this->handleQuery(ByNumber::create(['companyNumber' => $companyNumber]));
        } catch (NotFoundException) {
            $formHelper->setCompanyNotFoundError($form, $detailsFieldset);
            return $form;
        }

        if ($response->isOk()) {
            $formHelper->processCompanyNumberLookupForm(
                $form,
                $response->getResult(),
                $detailsFieldset,
                $addressFieldset
            );
        } else {
            $formHelper->setCompanyNotFoundError($form, $detailsFieldset);
        }

        return $form;
    }

    public function isValidCompanyNumber($companyNumber): bool
    {
        return strlen($companyNumber) === self::$companyNameLength;
    }
}
