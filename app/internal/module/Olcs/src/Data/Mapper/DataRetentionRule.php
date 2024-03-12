<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Class DataRetentionRule
 * @package Olcs\Data\Mapper
 */
class DataRetentionRule implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data Data from API
     *
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        $mappedData['id'] = $data['id'];
        $mappedData['description'] = $data['description'];
        $mappedData['retentionPeriod'] = $data['retentionPeriod'];
        $mappedData['maxDataSet'] = $data['maxDataSet'];
        $mappedData['isEnabled'] = $data['isEnabled'] ? 'Y' : 'N';
        $mappedData['actionType'] = $data['actionType']['id'];
        return ['ruleDetails' => $mappedData];
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data Data from form
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $mappedData['id'] = $data['ruleDetails']['id'];
        $mappedData['description'] = $data['ruleDetails']['description'];
        $mappedData['retentionPeriod'] = $data['ruleDetails']['retentionPeriod'];
        $mappedData['maxDataSet'] = $data['ruleDetails']['maxDataSet'];
        $mappedData['isEnabled'] = $data['ruleDetails']['isEnabled'] == 'Y' ? 1 : 0;
        $mappedData['actionType'] = $data['ruleDetails']['actionType'];
        return $mappedData;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form   The form
     * @param array         $errors Form errors
     *
     * @return array
     * @inheritdoc
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        return $errors;
    }
}
