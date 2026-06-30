<?php

declare(strict_types=1);

namespace Admin\Data\Mapper\Letter;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

class LetterSectionVariantEdit implements MapperInterface
{
    /**
     * Map data from a result array into an array suitable for a form.
     * Populates the form from variant query data (conditions + current version content).
     *
     * @param array $data Data from query
     * @return array
     */
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        return [
            'letterSectionVariant' => [
                'id' => $data['id'] ?? null,
                'goodsOrPsv' => $data['goodsOrPsv']['id'] ?? $data['goodsOrPsv'] ?? null,
                'isVariation' => $data['isVariation'] ?? null,
                'isNi' => $data['isNi'] ?? null,
                'organisationType' => $data['organisationType']['id'] ?? $data['organisationType'] ?? null,
                'letterChoice' => $data['letterChoice']['id'] ?? $data['letterChoice'] ?? null,
                'defaultContent' => $data['currentVersion']['defaultContent']
                    ?? $data['defaultContent']
                    ?? null,
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
        $commandData = $data['letterSectionVariant'] ?? [];

        // Convert empty strings to null for optional condition fields
        if (empty($commandData['goodsOrPsv'])) {
            $commandData['goodsOrPsv'] = null;
        }

        // Handle isVariation - convert to boolean or null
        if (isset($commandData['isVariation']) && $commandData['isVariation'] !== '') {
            $commandData['isVariation'] = (bool) (int) $commandData['isVariation'];
        } else {
            $commandData['isVariation'] = null;
        }

        // Handle isNi - convert to boolean or null
        if (isset($commandData['isNi']) && $commandData['isNi'] !== '') {
            $commandData['isNi'] = (bool) (int) $commandData['isNi'];
        } else {
            $commandData['isNi'] = null;
        }

        // Convert empty strings to null for optional fields
        if (empty($commandData['organisationType'])) {
            $commandData['organisationType'] = null;
        }

        if (empty($commandData['letterChoice'])) {
            $commandData['letterChoice'] = null;
        }

        // Content is already JSON from EditorJs, no additional processing needed

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
