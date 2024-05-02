<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Common\RefData;
use Common\Service\Qa\DataTransformer\ApplicationStepsPostDataTransformer;
use Laminas\Form\FormInterface;

/**
 * Class IrhpApplication
 * @package Olcs\Data\Mapper
 */
class IrhpApplication implements MapperInterface
{
    /**
     * Create service instance
     *
     *
     * @return IrhpApplication
     */
    public function __construct(private ApplicationStepsPostDataTransformer $applicationStepsPostDataTransformer)
    {
    }

    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        $formData['fields'] = $data;

        foreach ($formData['fields'] as $key => $value) {
            if (isset($value['id'])) {
                $formData['fields'][$key] = $value['id'];
            }
        }

        if (isset($data['id'])) {
            $formData['topFields']['id'] = $data['id'];
        }

        if (isset($data['dateReceived'])) {
            $formData['topFields']['dateReceived'] = $data['dateReceived'];
        }

        if (isset($data['status']['id'])) {
            $formData['topFields']['status'] = $data['status']['id'];
        }

        if (isset($data['declaration'])) {
            $formData['bottomFields']['declaration'] = $data['declaration'];
        }

        if (isset($data['checked'])) {
            $formData['bottomFields']['checked'] = $data['checked'];
        }

        if (isset($data['corCertificateNumber'])) {
            $formData['bottomFields']['corCertificateNumber'] = $data['corCertificateNumber'];
        }

        if (isset($data['irhpPermitType']['id'])) {
            $formData['topFields']['requiresPreAllocationCheck'] = $data['requiresPreAllocationCheck'];
            $formData['topFields']['isApplicationPathEnabled'] = $data['irhpPermitType']['isApplicationPathEnabled'];
            $formData['topFields']['irhpPermitType'] = $data['irhpPermitType']['id'];
        }

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $applicationSteps|null
     * @return array
     */
    public function mapFromForm(array $data, array $applicationSteps = null)
    {
        $additionalData = [];

        if ($data['topFields']['isApplicationPathEnabled']) {
            $cmdData['id'] = $data['topFields']['id'];
            $cmdData['dateReceived'] = $data['topFields']['dateReceived'];
            $cmdData['declaration'] = $data['bottomFields']['declaration'];

            $transformedQaData = $this->applicationStepsPostDataTransformer->getTransformed(
                $applicationSteps,
                $data['qa']
            );

            $cmdData['postData']['qa'] = $transformedQaData;

            if (isset($data['bottomFields']['checked'])) {
                $cmdData['checked'] = $data['bottomFields']['checked'];
            }

            if (isset($data['bottomFields']['corCertificateNumber'])) {
                $cmdData['corCertificateNumber'] = $data['bottomFields']['corCertificateNumber'];
            }

            return $cmdData;
        } elseif ($data['topFields']['irhpPermitType'] == RefData::IRHP_BILATERAL_PERMIT_TYPE_ID) {
            $selectedCountryIds = explode(',', $data['fields']['selectedCountriesCsv']);

            $countries = $data['fields']['countries'];
            $permitsRequired = [];

            foreach ($countries as $countryId => $periodData) {
                if (in_array($countryId, $selectedCountryIds)) {
                    $selectedPeriodId = $periodData['selectedPeriodId'];
                    $periods = $periodData['periods'];
                    $selectedPeriodKey = 'period' . $selectedPeriodId;

                    if (array_key_exists($selectedPeriodKey, $periods)) {
                        $permitsRequired[$countryId] = [
                            'periodId' => $selectedPeriodId,
                            'permitsRequired' => array_filter($periods[$selectedPeriodKey]),
                        ];
                    }
                }
            }

            $additionalData = ['permitsRequired' => $permitsRequired];
        }

        return array_merge($data['fields'], $data['bottomFields'], $data['topFields'], $additionalData);
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        unset($form);
        return $errors;
    }

    /**
     * Map the list of open windows/stocks/countries into right format for NoOfPermits form generation method
     *
     * @param int $permitTypeId
     * @return array
     */
    public static function mapApplicationData(array $irhpWindows, $permitTypeId, $formData = null)
    {
        $applicationData = [
            'irhpPermitType' => [
                'id' => $permitTypeId
            ]
        ];

        // If $formData is set we are editing, so populate an array of Country/Year/PermitsRequired to use in next step
        if ($formData) {
            $yearArr = [];
            if (isset($formData['fields']['irhpPermitApplications']) && is_array($formData['fields']['irhpPermitApplications'])) {
                foreach ($formData['fields']['irhpPermitApplications'] as $irhpPermitApplication) {
                    $irhpPermitStock = $irhpPermitApplication['irhpPermitWindow']['irhpPermitStock'];
                    $validFromYear = date('Y', strtotime($irhpPermitStock['validFrom']));
                    $countryId = $irhpPermitStock['country']['id'];

                    $yearArr[$countryId][$validFromYear] = $irhpPermitApplication['permitsRequired'];
                }
            }
        }

        // For each available window, prepare the data needed by the NoOfPermits fieldset builder
        foreach ($irhpWindows as $window) {
            $windowYear = date('Y', strtotime($window['irhpPermitStock']['validFrom']));
            $permitsRequired = 0;
            if (isset($yearArr[$window['irhpPermitStock']['country']['id']][$windowYear])) {
                $permitsRequired = $yearArr[$window['irhpPermitStock']['country']['id']][$windowYear];
            }

            $applicationData['irhpPermitApplications'][] = [
                'permitsRequired' => $permitsRequired,
                'irhpPermitWindow' => [
                    'irhpPermitStock' => $window['irhpPermitStock']
                ]
            ];
        }

        return $applicationData;
    }
}
