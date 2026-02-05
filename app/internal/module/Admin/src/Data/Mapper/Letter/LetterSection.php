<?php

declare(strict_types=1);

namespace Admin\Data\Mapper\Letter;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

class LetterSection implements MapperInterface
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
        // Get data from the current version if it exists
        $currentVersion = $data['currentVersion'] ?? [];

        $formData = [
            'letterSection' => [
                'id' => $data['id'] ?? null,
                'sectionKey' => $currentVersion['sectionKey'] ?? $data['sectionKey'] ?? null,
                'name' => $currentVersion['name'] ?? $data['name'] ?? null,
                'sectionType' => $currentVersion['sectionType'] ?? $data['sectionType'] ?? null,
                'defaultContent' => $currentVersion['defaultContent'] ?? $data['defaultContent'] ?? null,
                'goodsOrPsv' => $currentVersion['goodsOrPsv']['id'] ?? $data['goodsOrPsv']['id'] ?? null,
                'isNi' => $currentVersion['isNi'] ?? $data['isNi'] ?? false,
                'requiresInput' => $currentVersion['requiresInput'] ?? $data['requiresInput'] ?? false,
                'minLength' => $currentVersion['minLength'] ?? $data['minLength'] ?? null,
                'maxLength' => $currentVersion['maxLength'] ?? $data['maxLength'] ?? null,
                'helpText' => $currentVersion['helpText'] ?? $data['helpText'] ?? null,
                'publishFrom' => $currentVersion['publishFrom'] ?? $data['publishFrom'] ?? null,
            ]
        ];

        // Convert datetime string to array for DateTimeSelect field
        if (!empty($formData['letterSection']['publishFrom'])) {
            $publishFrom = new \DateTime($formData['letterSection']['publishFrom']);
            $formData['letterSection']['publishFrom'] = [
                'year' => $publishFrom->format('Y'),
                'month' => $publishFrom->format('m'),
                'day' => $publishFrom->format('d'),
                'hour' => $publishFrom->format('H'),
                'minute' => $publishFrom->format('i'),
                'second' => $publishFrom->format('s'),
            ];
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
        $commandData = $data['letterSection'] ?? [];

        // Convert boolean values
        if (isset($commandData['isNi'])) {
            $commandData['isNi'] = (bool) $commandData['isNi'];
        }

        if (isset($commandData['requiresInput'])) {
            $commandData['requiresInput'] = (bool) $commandData['requiresInput'];
        }

        // Handle empty selections
        if (empty($commandData['goodsOrPsv'])) {
            unset($commandData['goodsOrPsv']);
        }

        // publishFrom field removed from form - unset if present
        if (isset($commandData['publishFrom'])) {
            unset($commandData['publishFrom']);
        }

        // Add escape false for EditorJs content
        if (isset($commandData['defaultContent'])) {
            // Content is already JSON, no additional processing needed
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
