<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

/**
 * Class IrfoPsvAuth Mapper
 * @package Olcs\Data\Mapper
 */
class IrfoPsvAuth implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData['fields'] = $data;

        $formData['actions'] = $data['actions'];

        foreach ($formData['fields'] as $key => $value) {
            if (isset($value['id'])) {
                $formData['fields'][$key] = $value['id'];
            }
        }

        // Add status description as used for a label
        if (!empty($data['status']['description'])) {
            $formData['fields']['statusHtml'] = $data['status']['description'];
        }

        if (!empty($formData['fields']['createdOn'])) {
            // format createOn date
            $createdOn = new \DateTime($formData['fields']['createdOn']);
            $formData['fields']['createdOnHtml'] = $createdOn->format(\DATE_FORMAT);
        }

        if (!empty($formData['fields']['renewalDate'])) {
            // format renewalDate date
            $renewalDate = new \DateTime($formData['fields']['renewalDate']);
            $formData['fields']['renewalDateHtml'] = $renewalDate->format(\DATE_FORMAT);
        }

        // default all copies fields to 0
        $formData['fields'] = array_merge(
            [
                'copiesIssued' => 0,
                'copiesIssuedTotal' => 0,
                'copiesRequired' => 0,
                'copiesRequiredTotal' => 0,
            ],
            $formData['fields']
        );

        // copies fields
        $formData['fields']['copiesIssuedHtml'] = $formData['fields']['copiesIssued'];
        $formData['fields']['copiesIssuedTotalHtml'] = $formData['fields']['copiesIssuedTotal'];

        // calculate NonChargeable field
        $formData['fields']['copiesRequiredNonChargeable']
            = (int)$formData['fields']['copiesRequiredTotal'] - (int)$formData['fields']['copiesRequired'];

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
        $data['fields']['action'] = self::determineAction($data);
        return $data['fields'];
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
        return $errors;
    }

    /**
     * Determines the action being performed based on posted data
     *
     * @param $data
     * @return null
     */
    public static function determineAction($data)
    {
        $allActions = ['grant', 'approve', 'generateDocument', 'cns', 'withdraw', 'refuse', 'reset'];
        foreach ($allActions as $action) {
            if (isset($data['form-actions'][$action]) && !is_null($data['form-actions'][$action])) {
                return $action;
            }
        }

        return null;
    }

    /**
     * Determines which DTO to use based on action button pressed
     *
     * @param $data
     * @return mixed
     */
    public static function determineUpdateDto($data)
    {
        $action = self::determineAction($data);
        switch ($action) {
            case "grant":
                return \Dvsa\Olcs\Transfer\Command\Irfo\GrantIrfoPsvAuth;
            default:
                return \Dvsa\Olcs\Transfer\Command\Irfo\UpdateIrfoPsvAuth;
        }
    }
}
