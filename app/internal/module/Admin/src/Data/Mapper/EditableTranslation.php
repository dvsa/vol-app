<?php

namespace Admin\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Editable Translation Mapper
 *
 * @package Admin\Data\Mapper
 */
class EditableTranslation implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data Data from command
     *
     * @return array
     */
    public static function mapFromResult(array $data): array
    {
        return $data;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data Data from form
     *
     * @return array
     */
    public static function mapFromForm(array $data): array
    {
        foreach ($data['fields']['translationsArray'] as $isoCode => $translation) {
            if (empty($translation)) {
                unset($data['fields']['translationsArray'][$isoCode]);
            } else {
                $data['fields']['translationsArray'][$isoCode] = base64_encode((string) $data['fields']['translationsArray'][$isoCode]);
            }
        }

        return $data['fields'];
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form   Form interface
     * @param array         $errors array response from errors
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors): array
    {
        return $errors;
    }
}
