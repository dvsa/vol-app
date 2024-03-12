<?php

namespace Olcs\Data\Mapper;

use Laminas\Form\FormInterface;

/**
 * Operator transfer mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatorTransfer
{
    public const ERR_INVALID_ID = 'ERR_INVALID_ID';
    public const ERR_NO_LICENCES = 'ERR_NO_LICENCES';
    public const DEFAULT_KEY = 'toOperatorId';

    /**
     * Map from errors
     *
     * @param FormInterface $form   form
     * @param array         $errors errors
     *
     * @return void
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        $mapRules = [
            self::ERR_NO_LICENCES => 'licenceIds',
            self::ERR_INVALID_ID => 'toOperatorId',
        ];
        $formMessages = [];
        foreach ($errors as $key => $message) {
            if (array_key_exists($key, $mapRules)) {
                $formMessages[$mapRules[$key]][] = $message;
            }
        }
        $form->setMessages(
            (count($formMessages) > 0)
            ? $formMessages
            : [self::DEFAULT_KEY => ['form.operator-merge.to-operator-id.validation']]
        );
    }
}
