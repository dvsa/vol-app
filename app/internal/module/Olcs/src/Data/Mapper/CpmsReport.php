<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Class CpmsReport Mapper
 * @package Olcs\Data\Mapper
 */
class CpmsReport implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        return $data;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $commandData['reportCode'] = $data['reportOptions']['reportCode'];
        $commandData['start'] = $data['reportOptions']['startDate'];
        $commandData['end'] = $data['reportOptions']['endDate'];

        return $commandData;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @return array
     * @inheritdoc
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        return $errors;
    }
}
