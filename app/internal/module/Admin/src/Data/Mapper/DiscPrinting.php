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
            $retv[] = array(
                'value' => $id,
                'label' => $result
            );
        }
        return $retv;
    }

    public static function mapFromForm($params)
    {
        $data = [];
        $data['niFlag'] =
            isset($params['operator-location']['niFlag']) ? $params['operator-location']['niFlag'] :
                (isset($params['niFlag']) ? $params['niFlag'] : '');
        $data['operatorType'] =
            isset($params['operator-type']['goodsOrPsv']) ? $params['operator-type']['goodsOrPsv'] :
                (isset($params['operatorType']) ? $params['operatorType'] : '');
        $data['licenceType'] =
            isset($params['licence-type']['licenceType']) ? $params['licence-type']['licenceType'] :
                (isset($params['licenceType']) ? $params['licenceType'] : '');
        $data['startNumber'] =
            isset($params['discs-numbering']['startNumber']) ? $params['discs-numbering']['startNumber'] :
                (isset($params['startNumberEntered']) ? $params['startNumberEntered'] : null);
        $data['discSequence'] =
            isset($params['prefix']['discSequence']) ? $params['prefix']['discSequence'] :
                (isset($params['discSequence']) ? $params['discSequence'] : '');
        $data['discPrefix'] = isset($params['discPrefix']) ? $params['discPrefix'] : '';
        $data['isSuccessfull'] = isset($params['isSuccessfull']) ? $params['isSuccessfull'] : '';
        $data['endNumber'] = isset($params['endNumber']) ? $params['endNumber'] : '';
        $data['queueId'] = isset($params['queueId']) ? $params['queueId'] : '';
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
