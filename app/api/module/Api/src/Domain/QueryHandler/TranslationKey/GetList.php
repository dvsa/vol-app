<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TranslationKey;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Translation key list
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'TranslationKey';
}
