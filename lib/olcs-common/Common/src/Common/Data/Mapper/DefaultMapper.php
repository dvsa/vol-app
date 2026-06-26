<?php

namespace Common\Data\Mapper;

use Laminas\Form\FormInterface;

/**
 * Default data mapper
 */
class DefaultMapper implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data Data from command
     */
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        return ['fields' => $data];
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data Data from form
     */
    public static function mapFromForm(array $data): array
    {
        return $data['fields'];
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form   Form interface
     * @param array         $errors array response from errors
     */
    public static function mapFromErrors(FormInterface $form, array $errors): array
    {
        return $errors;
    }
}
