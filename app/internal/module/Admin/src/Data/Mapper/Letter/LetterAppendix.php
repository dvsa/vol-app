<?php

declare(strict_types=1);

namespace Admin\Data\Mapper\Letter;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

class LetterAppendix implements MapperInterface
{
    /**
     * Map data from a result array into an array suitable for a form
     * For a versioned entity, we get data from the current version
     *
     * @param array $data Data from query
     * @return array
     */
    public static function mapFromResult(array $data): array
    {
        $currentVersion = $data['currentVersion'] ?? [];

        $formData = [
            'letterAppendix' => [
                'id' => $data['id'] ?? null,
                'appendixKey' => $data['appendixKey'] ?? null,
                'name' => $currentVersion['name'] ?? $data['name'] ?? null,
                'description' => $currentVersion['description'] ?? $data['description'] ?? null,
                'appendixType' => $currentVersion['appendixType'] ?? $data['appendixType'] ?? 'pdf',
                'defaultContent' => $currentVersion['defaultContent'] ?? $data['defaultContent'] ?? null,
                'document' => isset($currentVersion['document']['id'])
                    ? $currentVersion['document']['id']
                    : (isset($data['document']['id']) ? $data['document']['id'] : null),
            ]
        ];

        // Convert defaultContent array to JSON string for EditorJS field
        if (is_array($formData['letterAppendix']['defaultContent'])) {
            $formData['letterAppendix']['defaultContent'] = json_encode($formData['letterAppendix']['defaultContent']);
        }

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
        $commandData = $data['letterAppendix'] ?? [];

        // Document is handled via file upload in the controller, not via form data
        unset($commandData['document']);

        return $commandData;
    }

    /**
     * Map errors onto the form
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
