<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

/**
 * Class Appeal Mapper for forms with Fields field set
 * @package Olcs\Data\Mapper
 */
class Appeal implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData['fields'] = $data;

        foreach ($formData['fields'] as $key => $value) {
            if (isset($value['id'])) {
                $formData['fields'][$key] = $value['id'];
            }
        }

        if (!empty($formData['fields']['withdrawnDate'])) {
            $formData['fields']['isWithdrawn'] = 'Y';
        }

        $formData['fields']['dvsaNotified'] = $data['dvsaNotified'] ? 'Y' : 'N';

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
        $data = $data['fields'];

        if (isset($data['isWithdrawn']) && $data['isWithdrawn'] == 'N') {
            $data['withdrawnDate'] = null;
        }

        return $data;
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
}
