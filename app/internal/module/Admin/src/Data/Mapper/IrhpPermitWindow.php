<?php

namespace Admin\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * IRHP Permit window mapper
 *
 * @package Admin\Data\Mapper
 */
class IrhpPermitWindow implements MapperInterface
{
    public const PERMIT_WINDOW_DETAILS = 'permitWindowDetails';

    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data Data from command
     *
     * @return array
     */
    public static function mapFromResult(array $data): array
    {
        $mappedData[self::PERMIT_WINDOW_DETAILS] = $data;
        $mappedData[self::PERMIT_WINDOW_DETAILS]['stockId'] = $data['stockId'] ?? $data['irhpPermitStock']['id'];
        return $mappedData;
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
        $data[self::PERMIT_WINDOW_DETAILS]['irhpPermitStock'] = $data[self::PERMIT_WINDOW_DETAILS]['stockId'];
        unset($data[self::PERMIT_WINDOW_DETAILS]['stockId']);
        return $data[self::PERMIT_WINDOW_DETAILS];
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
        $form->setMessages([self::PERMIT_WINDOW_DETAILS => $errors['messages']]);

        return $errors;
    }
}
