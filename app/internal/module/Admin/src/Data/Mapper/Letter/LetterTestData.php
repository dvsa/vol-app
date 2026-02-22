<?php

declare(strict_types=1);

namespace Admin\Data\Mapper\Letter;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

class LetterTestData implements MapperInterface
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
            'letterTestData' => [
                'id' => $data['id'] ?? null,
                'name' => $data['name'] ?? null,
                'json' => isset($data['json']) ? json_encode($data['json'], JSON_PRETTY_PRINT) : null,
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
        $commandData = $data['letterTestData'] ?? [];

        // Parse JSON string back to array/object
        if (isset($commandData['json']) && is_string($commandData['json'])) {
            $commandData['json'] = json_decode($commandData['json'], true);
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
