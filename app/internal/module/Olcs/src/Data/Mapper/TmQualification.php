<?php

/**
 * Tm Qualification mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Data\Mapper;

/**
 * Tm Qualification mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmQualification
{
    public static function mapFromForm(array $data)
    {
        $result = $data['qualification-details'];
        if (isset($data['transportManager'])) {
            $result['transportManager'] = $data['transportManager'];
        }
        return $result;
    }

    public static function mapFromErrors($form, array $errors)
    {
        $errors = $errors['messages'];
        $details = [
            'issuedDate',
            'serialNo',
            'qualificationType',
            'countryCode'
        ];
        $formMessages = [];
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $message) {
                if (in_array($field, $details)) {
                    $formMessages['qualification-details'][$field][] = $message;
                    unset($errors[$field]);
                }
            }
        }
        $form->setMessages($formMessages);
        return $errors;
    }

    public static function mapFromDocumentsResult(array $data)
    {
        return $data['result'] ?? [];
    }

    public static function mapFromResult(array $data)
    {
        if (isset($data['id'])) {
            $mapped = [
                'qualification-details' => [
                    'id' => $data['id'],
                    'version' => $data['version'],
                    'issuedDate' => $data['issuedDate'],
                    'serialNo' => $data['serialNo'],
                    'qualificationType' => $data['qualificationType']['id'],
                    'countryCode' => $data['countryCode']['id']
                ]
            ];
        } else {
            $mapped = [
                'qualification-details' => [
                    'countryCode' => 'GB'
                ]
            ];
        }
        $mapped['transportManager'] = $data['transportManager'];
        return $mapped;
    }
}
