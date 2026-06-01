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
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        // VOL-7305: the four chrome slot fields hydrate as JSON arrays from Doctrine.
        // EditorJS form expects a JSON STRING in the hidden input, so re-encode here.
        $encodeSlot = static fn($slot) => is_array($slot) ? json_encode($slot) : ($slot ?? null);

        $formData = [
            'masterTemplate' => [
                'id' => $data['id'] ?? null,
                'name' => $data['name'] ?? null,
                'templateContent' => $data['templateContent'] ?? null,
                'headerLeftContent' => $encodeSlot($data['headerLeftContent'] ?? null),
                'headerRightContent' => $encodeSlot($data['headerRightContent'] ?? null),
                'signoffContent' => $encodeSlot($data['signoffContent'] ?? null),
                'footerContent' => $encodeSlot($data['footerContent'] ?? null),
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

        // VOL-7305: EditorJS submits a JSON string per slot field. Decode to array
        // for the command DTO. Empty/whitespace → null so the API handler treats it
        // as "leave alone" rather than "clear".
        $decodeSlot = static function ($value): ?array {
            if (!is_string($value) || trim($value) === '') {
                return is_array($value) ? $value : null;
            }
            $decoded = json_decode($value, true);
            return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : null;
        };

        foreach (['headerLeftContent', 'headerRightContent', 'signoffContent', 'footerContent'] as $slot) {
            if (array_key_exists($slot, $commandData)) {
                $commandData[$slot] = $decodeSlot($commandData[$slot]);
            }
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
