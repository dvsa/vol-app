<?php

declare(strict_types=1);

namespace Admin\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

class LongText implements MapperInterface
{
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        if (isset($data['content']) && !is_string($data['content'])) {
            $data['content'] = json_encode($data['content'], JSON_THROW_ON_ERROR);
        }

        return ['longTextDetails' => $data];
    }

    public static function mapFromForm(array $data): array
    {
        return $data['longTextDetails'];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function mapFromErrors(FormInterface $form, array $errors): array
    {
        return $errors;
    }
}
