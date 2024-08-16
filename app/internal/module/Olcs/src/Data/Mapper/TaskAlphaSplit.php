<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Class TaskAlphaSplit
 * @package Olcs\Data\Mapper
 */
class TaskAlphaSplit implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData['taskAllocationRule'] = $data['taskAllocationRule'];

        if (!isset($data['id'])) {
            // if no ID key then must be new, therefore:
            return $formData;
        }

        $formData['taskAlphaSplit'] = [
            'id' => $data['id'],
            'version' => $data['version'],
            'user' => $data['user'],
            'letters' => $data['letters'],
        ];

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @return array
     */
    public static function mapFromForm(array $formData)
    {
        $data = [
            'id' => $formData['taskAlphaSplit']['id'],
            'version' => $formData['taskAlphaSplit']['version'],
            'letters' => $formData['taskAlphaSplit']['letters'],
            'user' => $formData['taskAlphaSplit']['user'],
            'taskAllocationRule' => !empty($formData['taskAllocationRule']) ? $formData['taskAllocationRule'] : null,
        ];

        return $data;
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
