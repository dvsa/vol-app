<?php

/**
 * Get a single translation key by id
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Transfer\Query\TranslationKey;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/translation-key/single")
 */
class ById extends AbstractQuery
{
    use Identity;

    /** @Transfer\Optional */
    protected $previewEditorJs;

    public function getPreviewEditorJs(): bool
    {
        return filter_var($this->previewEditorJs, FILTER_VALIDATE_BOOLEAN);
    }
}
