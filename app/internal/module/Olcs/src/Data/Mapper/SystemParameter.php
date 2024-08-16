<?php

/**
 * SystemParameter mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * SystemParameter mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SystemParameter implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData = [
            'system-parameter-details' => $data
        ];
        $formData['system-parameter-details']['hiddenId'] = $data['id'];

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $commandData = $data['system-parameter-details'];
        if (empty($commandData['id']) && !empty($data['system-parameter-details']['hiddenId'])) {
            $commandData['id'] = $data['system-parameter-details']['hiddenId'];
        }
        unset($commandData['hiddenId']);
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
        $messages = [];
        if (isset($errors['messages']['id'])) {
            $messages['system-parameter-details']['id'] = $errors['messages']['id'];
            unset($errors['messages']['id']);
        }
        if (isset($errors['messages']['paramValue'])) {
            $messages['system-parameter-details']['paramValue'] = $errors['messages']['paramValue'];
            unset($errors['messages']['paramValue']);
        }
        if (isset($errors['messages']['description'])) {
            $messages['system-parameter-details']['description'] = $errors['messages']['description'];
            unset($errors['messages']['description']);
        }
        $form->setMessages($messages);
        return $errors;
    }
}
