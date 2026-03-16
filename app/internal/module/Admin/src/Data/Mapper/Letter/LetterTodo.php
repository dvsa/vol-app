<?php

declare(strict_types=1);

namespace Admin\Data\Mapper\Letter;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

class LetterTodo implements MapperInterface
{
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        $currentVersion = $data['currentVersion'] ?? [];

        $formData = [
            'letterTodo' => [
                'id' => $data['id'] ?? null,
                'todoKey' => $data['todoKey'] ?? null,
                'description' => $currentVersion['description'] ?? $data['description'] ?? null,
                'helpText' => $currentVersion['helpText'] ?? $data['helpText'] ?? null,
            ]
        ];

        // Convert description array to JSON string for EditorJS field
        if (is_array($formData['letterTodo']['description'])) {
            $formData['letterTodo']['description'] = json_encode($formData['letterTodo']['description']);
        }

        return $formData;
    }

    public static function mapFromForm(array $data): array
    {
        return $data['letterTodo'] ?? [];
    }

    public static function mapFromErrors(FormInterface $form, array $errors): array
    {
        return $errors;
    }
}
