<?php

namespace Admin\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * IRHP Permit stock mapper
 *
 * @package Admin\Data\Mapper
 */
class DocumentTemplate implements MapperInterface
{
    public const TEMPLATE_PATH_PREFIXES = [
        'templates' => 'root',
        'templates/NI' => 'ni',
        'templates/GB' => 'gb',
        'templates/Image' => 'image',
        'guides' => 'guides'
    ];

    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data Data from command
     *
     * @return array
     */
    public static function mapFromResult(array $data): array
    {
        $formData = [];
        $formData['fields'] = $data;
        if (!empty($data)) {
            $formData['fields']['category'] = $data['category']['id'];
            $formData['fields']['subCategory'] = $data['subCategory']['id'];

            $pathParts = pathinfo($data['document']['identifier']);
            $formData['fields']['templateFolder'] =
                array_key_exists(ltrim($pathParts['dirname'], '/'), self::TEMPLATE_PATH_PREFIXES)
                    ? self::TEMPLATE_PATH_PREFIXES[ltrim($pathParts['dirname'], '/')]
                    : '';
        }

        return $formData;
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
