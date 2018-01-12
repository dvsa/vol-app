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
     * @param array $data
     *
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        return $data;
    }

    /**
     * @param $formData
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

    public static function mapFromErrors($form, array $errors)
    {

        return $errors;
    }
}
