<?php

/**
 * Get a list of translation keys
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Transfer\Query\TranslationKey;

use Dvsa\Olcs\Transfer\FieldType\Traits\TranslationSearchOptional;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;

/**
 * @Transfer\RouteName("backend/translation-key")
 */
class GetList extends AbstractQuery implements OrderedQueryInterface, PagedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;
    use TranslationSearchOptional;
}
