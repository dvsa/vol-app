<?php

declare(strict_types=1);

namespace Admin\Data\Mapper\Letter;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

class MasterTemplate implements MapperInterface
{
    /**
     * Map data from a result array into an array suitable for a form
     *
     * @param array $data Data from query
     * @return array
     */
    public static function mapFromResult(array $data): array
    {
        $formData = [
            'masterTemplate' => [
                'id' => $data['id'] ?? null,
                'name' => $data['name'] ?? null,
                'templateContent' => $data['templateContent'] ?? null,
                'isDefault' => $data['isDefault'] ?? false,
                'locale' => $data['locale'] ?? null,
            ]
        ];

        return $formData;
    }

    /**
     * Map form data back into a command data structure
     *
     * @param array $data Data from form
     * @return array
     */
    public static function mapFromForm(array $data): array
    {
        $commandData = $data['masterTemplate'] ?? [];

        // Ensure boolean value for isDefault
        if (isset($commandData['isDefault'])) {
            $commandData['isDefault'] = (bool) $commandData['isDefault'];
        }

        return $commandData;
    }

    /**
     * Map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form Form interface
     * @param array $errors array response from errors
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors): array
    {
        return $errors;
    }
}
