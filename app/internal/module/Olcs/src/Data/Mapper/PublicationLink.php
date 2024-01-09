<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;
use Olcs\Module;

/**
 * Class Generic Mapper for forms with Fields field set
 * @package Olcs\Data\Mapper
 */
class PublicationLink implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        $readOnly = [
            'typeArea' => $data['publication']['pubType'] . ' / ' . $data['publication']['trafficArea']['name'],
            'publicationNo' => $data['publication']['publicationNo'],
            'status' => $data['publication']['pubStatus']['description'],
            'section' => $data['publicationSection']['description'],
            'trafficArea' => $data['publication']['trafficArea']['name'],
            'publicationDate' => date(Module::$dateFormat, strtotime($data['publication']['pubDate']))
        ];

        $textFields = [
            'text1' => $data['text1'],
            'text2' => $data['text2'],
            'text3' => $data['text3']
        ];

        if ($data['isNew']) {
            $base = [
                'id' => $data['id'],
                'version' => $data['version']
            ];

            $formData = [
                'fields' => array_merge($textFields, $base)
            ];
        } else {
            $formData = [
                'readOnlyText' => $textFields
            ];
        }

        $formData['readOnly'] = $readOnly;

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
