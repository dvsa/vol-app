<?php

namespace Admin\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

class MessagingSubject implements MapperInterface
{
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        if (!isset($data['id'])) {
            // new categories start active
            return ['details' => ['isActive' => '1']];
        }

        return [
            'details' => [
                'id' => $data['id'],
                'version' => $data['version'],
                'description' => $data['description'],
                'category' => $data['category']['id'],
                'subCategory' => $data['subCategory']['id'] ?? null,
                'isActive' => $data['isActive'] ? '1' : '0',
            ],
        ];
    }

    public static function mapFromForm(array $data): array
    {
        $details = $data['details'];

        $details['isActive'] = (bool) $details['isActive'];

        return $details;
    }

    public static function mapFromErrors(FormInterface $form, array $errors): array
    {
        return $errors;
    }
}
