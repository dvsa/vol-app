<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * System Info Message mapper
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class SystemInfoMessage implements MapperInterface
{
    public const DETAILS = 'details';

    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        return [
            self::DETAILS => $data,
        ];
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        return $data[self::DETAILS];
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form
     * @param array         $errors
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        $form->setMessages([self::DETAILS => $errors['messages']]);

        return $errors;
    }
}
