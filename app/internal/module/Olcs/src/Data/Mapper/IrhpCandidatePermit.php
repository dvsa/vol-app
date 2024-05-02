<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Class IrhpCandidatePermit
 * @package Olcs\Data\Mapper
 */
class IrhpCandidatePermit implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        $form['fields']['irhpPermitRangeSelected'] = $data['irhpPermitRange']['id'];
        $form['fields']['irhpPermitApplication'] = $data['irhpPermitApplication']['id'];
        if (isset($data['id'])) {
            $form['fields']['id'] = $data['id'];
        }
        return $form;
    }

    /**
     * Maps required application data into array for view template
     *
     * @return array
     */
    public static function mapApplicationData(array $data)
    {
        $applicationData = [];
        $applicationData['requiredEuro5'] = $data['irhpPermitApplications'][0]['requiredEuro5'];
        $applicationData['requiredEuro6'] = $data['irhpPermitApplications'][0]['requiredEuro6'];
        $applicationData['countries'] = isset($data['countrys']) && is_array($data['countrys']) ?
            implode(', ', array_column($data['countrys'], 'countryDesc')) : '';

        return $applicationData;
    }

    /**
     * map form data back into a command data structure
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $data['fields']['irhpApplication'] = $data['fields']['irhpAppId'];
        $data['fields']['irhpPermitApplication'] = $data['fields']['permitAppId'];
        return $data['fields'];
    }

    /**
     * map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        unset($form);
        return $errors;
    }
}
