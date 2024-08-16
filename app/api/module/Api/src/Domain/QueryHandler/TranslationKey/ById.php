<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TranslationKey;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Retrieve a translation key by ID
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class ById extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'TranslationKey';
    protected $bundle = [
        'translationKeyTexts' => ['language'],
        'translationKeyCategoryLinks' => ['category', 'subCategory']
    ];
}
