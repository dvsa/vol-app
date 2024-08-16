<?php

/**
 * Disc Printing mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Admin\Data\Mapper;

/**
 * Disc Printing mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DiscPrinting
{
    public static function mapFromResultForPrefixes(array $prefixes)
    {
        $retv = [];
        // sort prefixes alphabetically by label
        asort($prefixes);
        foreach ($prefixes as $id => $result) {
            $retv[] = [
                'value' => $id,
                'label' => $result
            ];
        }
        return $retv;
    }

    public static function mapFromForm($params)
    {
        $data = [];
        $data['niFlag'] =
            $params['operator-location']['niFlag'] ?? $params['niFlag'] ?? '';
        $data['operatorType'] =
            $params['operator-type']['goodsOrPsv'] ?? $params['operatorType'] ?? '';
        $data['licenceType'] =
            $params['licence-type']['licenceType'] ?? $params['licenceType'] ?? '';
        $data['startNumber'] =
            $params['discs-numbering']['startNumber'] ?? $params['startNumberEntered'] ?? null;
        $data['discSequence'] =
            $params['prefix']['discSequence'] ?? $params['discSequence'] ?? '';
        $data['maxPages'] = (isset($params['discs-numbering']) && isset($params['discs-numbering']['maxPages']))
            ? $params['discs-numbering']['maxPages']
            : ($params['maxPages'] ?? null);
        $data['discPrefix'] = $params['discPrefix'] ?? '';
        $data['isSuccessfull'] = $params['isSuccessfull'] ?? '';
        $data['endNumber'] = $params['endNumber'] ?? '';
        $data['queueId'] = $params['queueId'] ?? '';
        return $data;
    }

    public static function mapFromErrors($form, $errors)
    {
        if (isset($errors['startNumber']['err_decr'])) {
            $messages = [
                'discs-numbering' => ['startNumber' => [$errors['startNumber']['err_decr']]]
            ];
            $form->setMessages($messages);
            unset($errors['startNumber']);
        }
        return $errors;
    }
}
