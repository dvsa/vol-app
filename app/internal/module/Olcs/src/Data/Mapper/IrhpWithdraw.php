<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\Form;
use Laminas\Form\FormInterface;

/**
 * Class IrhpWithdraw
 * @package Olcs\Data\Mapper
 */
class IrhpWithdraw implements MapperInterface
{
    /**
     * Should map form data back into a command data structure
     *
     * @param array $data
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $cmdData['id'] = $data['id'];
        $cmdData['reason'] = $data['withdraw-details']['reason'];
        return $cmdData;
    }

    public static function mapFromResult(array $data)
    {
        return $data;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param Form $form
     * @param array $errors
     * @return array
     */
    public static function mapFromErrors(Form $form, array $errors)
    {
        return $errors;
    }
}
