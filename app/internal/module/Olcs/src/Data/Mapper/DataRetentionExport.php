<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

/**
 * Class DataRetentionExport
 */
class DataRetentionExport implements MapperInterface
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
        return $data;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data Form data
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $commandData['dataRetentionRuleId'] = $data['exportOptions']['rule'];
        $commandData['startDate'] = $data['exportOptions']['startDate'];
        $commandData['endDate'] = $data['exportOptions']['endDate'];

        return $commandData;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form   Form
     * @param array         $errors Error messages
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        return $errors;
    }
}
