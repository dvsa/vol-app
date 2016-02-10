<?php

/**
 * Printer Exception mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

/**
 * Printer Exception mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrinterException implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData = [
            'exception-details' => [
                'id' => isset($data['id']) ? $data['id'] : null,
                'version' => isset($data['version']) ? $data['version'] : null,
                'teamOrUser' => isset($data['user']['id']) ? 'user' : 'team',
                'team' => isset($data['team']['id']) ? $data['team']['id'] :
                    (isset($data['team']) ? $data['team'] : null)
            ],
            'team-printer' => [
                'printer' => isset($data['printer']['id']) ? $data['printer']['id'] : null,
                'subCategoryTeam' => isset($data['subCategory']['id']) ? $data['subCategory']['id'] : null,
                'categoryTeam' => isset($data['subCategory']['category']['id']) ?
                    $data['subCategory']['category']['id'] : null
            ],
            'user-printer' => [
                'printer' => isset($data['printer']['id']) ? $data['printer']['id'] : null,
                'subCategoryUser' => isset($data['subCategory']['id']) ? $data['subCategory']['id'] : null,
                'categoryUser' => isset($data['subCategory']['category']['id']) ?
                    $data['subCategory']['category']['id'] : null,
                'user' => isset($data['user']['id']) ? $data['user']['id'] : null
            ],
        ];

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
        if (isset($data['exception-details']['teamOrUser']) && $data['exception-details']['teamOrUser'] === 'user') {
            $commandData = [
                'user' => $data['user-printer']['user'],
                'subCategory' => $data['user-printer']['subCategoryUser'],
                'printer' => $data['user-printer']['printer']
            ];
        } else {
            $commandData = [
                'subCategory' => $data['team-printer']['subCategoryTeam'],
                'printer' => $data['team-printer']['printer']
            ];
        }
        if (isset($data['exception-details']['version'])) {
            $commandData['version'] = $data['exception-details']['version'];
        }
        if (isset($data['exception-details']['id'])) {
            $commandData['id'] = $data['exception-details']['id'];
        }
        if (isset($data['exception-details']['team'])) {
            $commandData['team'] = $data['exception-details']['team'];
        }
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
        if (isset($errors['messages']['subCategory'])) {
            $messages['team-printer']['subCategoryTeam'] = $errors['messages']['subCategory'];
            $messages['user-printer']['subCategoryUser'] = $errors['messages']['subCategory'];
            unset($errors['messages']['subCategory']);
        }
        if (isset($errors['messages']['printer'])) {
            $messages['team-printer']['printer'] = $errors['messages']['printer'];
            $messages['user-printer']['printer'] = $errors['messages']['printer'];
            unset($errors['messages']['printer']);
        }
        if (isset($errors['messages']['user'])) {
            $messages['user-printer']['user'] = $errors['messages']['user'];
            unset($errors['messages']['user']);
        }
        $form->setMessages($messages);
        return $errors;
    }
}
