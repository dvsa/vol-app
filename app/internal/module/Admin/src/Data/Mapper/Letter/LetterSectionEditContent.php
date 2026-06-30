<?php

declare(strict_types=1);

namespace Admin\Data\Mapper\Letter;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

class LetterSectionEditContent implements MapperInterface
{
    /**
     * Map data from a result array into an array suitable for the edit content form.
     * Extracts id and defaultContent from the current version.
     *
     * @param array $data Data from query
     * @return array
     */
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        $currentVersion = $data['currentVersion'] ?? [];

        return [
            'letterSectionEditContent' => [
                'id' => $data['id'] ?? null,
                'defaultContent' => $currentVersion['defaultContent'] ?? $data['defaultContent'] ?? null,
            ]
        ];
    }

    /**
     * Map form data back into a command data structure.
     * Returns id + defaultContent for the update command.
     *
     * @param array $data Data from form
     * @return array
     */
    public static function mapFromForm(array $data): array
    {
        $formData = $data['letterSectionEditContent'] ?? [];

        return [
            'id' => $formData['id'] ?? null,
            'defaultContent' => $formData['defaultContent'] ?? null,
        ];
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
