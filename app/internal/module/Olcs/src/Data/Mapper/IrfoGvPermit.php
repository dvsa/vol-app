<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Class IrfoGvPermit Mapper
 * @package Olcs\Data\Mapper
 */
class IrfoGvPermit implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        if (!empty($data['now'])) {
            $now = $data['now'];
            unset($data['now']);
        } else {
            $now = new \DateTime();
        }

        $formData['fields'] = $data;

        foreach ($formData['fields'] as $key => $value) {
            if (isset($value['id'])) {
                $formData['fields'][$key] = $value['id'];
            }
        }

        if (empty($formData['fields']['yearRequired'])) {
            // defaults to the current year
            $formData['fields']['yearRequired'] = $now->format('Y');
        }

        if (empty($formData['fields']['inForceDate'])) {
            // defaults to now
            $formData['fields']['inForceDate'] = $now;
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
}
