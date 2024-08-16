<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TranslationKey;

use Dvsa\Olcs\Api\Domain\QueryHandler\TranslationKey\ById as TranslationKeyByIdHandler;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKey as TranslationKeyRepo;
use Dvsa\Olcs\Transfer\Query\TranslationKey\ById as QryClass;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractQueryByIdHandlerTest;
use Dvsa\Olcs\Api\Entity\System\TranslationKey as TranslationKeyEntity;

/**
 * ById Test
 *
 * @author Andy Newtom <andy@vitri.ltd>
 */
class ByIdTest extends AbstractQueryByIdHandlerTest
{
    protected $sutClass = TranslationKeyByIdHandler::class;
    protected $sutRepo = 'TranslationKey';
    protected $bundle = [
        'translationKeyTexts' => ['language'],
        'translationKeyCategoryLinks' => ['category', 'subCategory']
    ];
    protected $qryClass = QryClass::class;
    protected $repoClass = TranslationKeyRepo::class;
    protected $entityClass = TranslationKeyEntity::class;
}
