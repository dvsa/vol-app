<?php

namespace Admin\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

/**
 * IRHP Permit window mapper
 *
 * @package Admin\Data\Mapper
 */
class IrhpPermitWindow implements MapperInterface
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
        $mappedData['permitWindowDetails'] = $data;
        $mappedData['permitWindowDetails']['parentId'] = isset($data['parentId']) ? $data['parentId'] : $data['irhpPermitStock']['id'];
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
        $data['permitWindowDetails']['irhpPermitStock'] = $data['permitWindowDetails']['parentId'];
        unset($data['permitWindowDetails']['parentId']);
        return $data['permitWindowDetails'];
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
