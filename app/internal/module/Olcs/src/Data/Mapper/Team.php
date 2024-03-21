<?php

/**
 * Team mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Team mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Team implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData = [
            'team-details' => $data
        ];
        if (is_array($data['teamPrinters']) && count($data['teamPrinters'])) {
            $defaultPrinter = null;
            foreach ($data['teamPrinters'] as $tp) {
                if (isset($tp['printer']) && !$tp['user'] && !$tp['subCategory']) {
                    $defaultPrinter = $tp['printer'];
                    break;
                }
            }
            $formData['team-details']['defaultPrinter'] = $defaultPrinter;
        }

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
        $commandData = $data['team-details'];
        return $commandData;
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
        $messages = [];
        if (isset($errors['messages']['name'])) {
            $messages['team-details']['name'] = $errors['messages']['name'];
            unset($errors['messages']['name']);
        }
        if (isset($errors['messages']['description'])) {
            $messages['team-details']['description'] = $errors['messages']['description'];
            unset($errors['messages']['description']);
        }
        if (isset($errors['messages']['trafficArea'])) {
            $messages['team-details']['trafficArea'] = $errors['messages']['trafficArea'];
            unset($errors['messages']['trafficArea']);
        }
        $form->setMessages($messages);
        return $errors;
    }
}
