<?php

/**
 * Document relink mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Olcs\Data\Mapper;

/**
 * Document relink mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DocumentRelink
{
    public static function mapFromForm(array $data)
    {
        $result = [
            'type' => $data['document-relink-details']['type'],
            'targetId' => $data['document-relink-details']['targetId'],
            'ids' => explode(',', $data['document-relink-details']['ids'])
        ];
        return $result;
    }

    public static function mapFromErrors($form, array $errors)
    {
        $errors = $errors['messages'];
        $details = [
            'targetId',
            'type'
        ];
        $formMessages = [];
        foreach ($errors as $field => $message) {
            if (in_array($field, $details)) {
                $formMessages['document-relink-details'][$field][] = $message;
                unset($errors[$field]);
            }
        }
        $form->setMessages($formMessages);
        return $errors;
    }
}
