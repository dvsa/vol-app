<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;

/**
 * Class RevocationsSla
 *
 * @package Olcs\Data\Mapper
 */
class RevocationsSla implements MapperInterface
{
    /**
     * @param array $data data
     *
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        if (isset($data['id'])) {
            $formData['id'] = $data['id'];
            unset($data['id']);
        }

        if (isset($data['version'])) {
            $formData['version'] = $data['version'];
            unset($data['version']);
        }

        $formData['fields'] = $data;

        foreach ($formData['fields'] as $key => $value) {
            if (!in_array($key, ['id', 'version'])) {
                if (isset($value['id'])) {
                    $formData['fields'][$key] = $value['id'];
                }
            }
        }
        return $formData;
    }

    /**
     * Map from form
     *
     * @param array $formData form data
     *
     * @return array
     */
    public static function mapFromForm($formData)
    {
        $data = [];
        foreach ($formData['fields'] as $field => $value) {
            $data[$field] = $value;
        }
        $data['id'] = $formData['id'];
        $data['version'] = $formData['version'];
        return $data;
    }

    /**
     * map form errors
     *
     * @param mixed $form   form
     * @param array $errors errors
     *
     * @return array
     */
    public static function mapFromErrors($form, array $errors)
    {
        return $errors;
    }
}
