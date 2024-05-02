<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Class OperatorPeople Mapper
 * @package Olcs\Data\Mapper
 */
class OperatorPeople implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData = ['organisation' => $data['organisation']];
        if (isset($data['person'])) {
            $formData['data']['id'] = $data['id'];
            $formData['data']['version'] = $data['version'];
            $formData['data']['title'] = $data['person']['title']['id'];
            $formData['data']['forename'] = $data['person']['forename'];
            $formData['data']['familyName'] = $data['person']['familyName'];
            $formData['data']['otherName'] = $data['person']['otherName'];
            $formData['data']['position'] = $data['position'];

            if (!empty($data['person']['birthDate'])) {
                $birthDate = new \DateTime($data['person']['birthDate']);
                $formData['data']['birthDate'] = [
                    'day' => $birthDate->format('d'),
                    'month' => $birthDate->format('m'),
                    'year' => $birthDate->format('Y'),
                ];
            }
        }

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @return array
     */
    public static function mapFromForm(array $formData)
    {
        $commandData = [
            'id' => $formData['data']['id'],
            'version' => $formData['data']['version'],
            'person' => [
                'title' => $formData['data']['title'],
                'forename' => $formData['data']['forename'],
                'familyName' => $formData['data']['familyName'],
                'otherName' => $formData['data']['otherName'],
                'birthDate' => empty($formData['data']['birthDate']) ? '' : $formData['data']['birthDate'],
            ],
        ];

        // required if creating
        if (isset($formData['organisation'])) {
            $commandData['organisation'] = $formData['organisation'];
        }

        // only used if org type is "other"
        if (isset($formData['data']['position'])) {
            $commandData['position'] = $formData['data']['position'];
        }

        return $commandData;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        return $errors;
    }
}
