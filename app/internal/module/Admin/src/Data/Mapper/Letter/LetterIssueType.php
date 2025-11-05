<?php

declare(strict_types=1);

namespace Admin\Data\Mapper\Letter;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

class LetterIssueType implements MapperInterface
{
    /**
     * Map data from a result array into an array suitable for a form
     *
     * @param array $data Data from query
     * @return array
     */
    public static function mapFromResult(array $data): array
    {
        return [
            'letterIssueType' => [
                'id' => $data['id'] ?? null,
                'code' => $data['code'] ?? null,
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
                'displayOrder' => $data['displayOrder'] ?? null,
                'isActive' => $data['isActive'] ?? true,
            ]
        ];
    }

    /**
     * Map form data back into a command data structure
     *
     * @param array $data Data from form
     * @return array
     */
    public static function mapFromForm(array $data): array
    {
        $commandData = $data['letterIssueType'] ?? [];

        // Convert boolean values
        if (isset($commandData['isActive'])) {
            $commandData['isActive'] = (bool) $commandData['isActive'];
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
