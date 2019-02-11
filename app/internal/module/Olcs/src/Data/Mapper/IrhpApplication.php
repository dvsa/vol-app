<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

/**
 * Class IrhpApplication
 * @package Olcs\Data\Mapper
 */
class IrhpApplication implements MapperInterface
{
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

        if (isset($data['declaration'])) {
            $formData['bottomFields']['declaration'] = $data['declaration'];
        }

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        return array_merge($data['fields'], $data['bottomFields'], $data['topFields']);
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form
     * @param array $errors
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        unset($form);
        return $errors;
    }

    /**
     * Map the list of open windows/stocks/countries into right format for NoOfPermits form generaion method
     *
     * @param array $irhpWindows
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
            foreach ($formData['fields']['irhpPermitApplications'] as $irhpPermitApplication) {
                $irhpPermitStock = $irhpPermitApplication['irhpPermitWindow']['irhpPermitStock'];
                $validFromYear = date('Y', strtotime($irhpPermitStock['validFrom']));
                $countryId = $irhpPermitStock['country']['id'];

                $yearArr[$countryId][$validFromYear] = $irhpPermitApplication['permitsRequired'];
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
