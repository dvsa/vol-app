<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\System\SystemParameter;

/**
 * Page margins for PSVDiscTemplateGotenberg (token: Psv_Disc_Margins).
 * Defaults place the Valid/Expires/Lic No block per the PSV423 scan,
 * clear of the pre-printed RESTRICTED arc.
 */
class PsvDiscMargins extends AbstractDiscMargins
{
    public const TOP_PARAM = SystemParameter::PSV_DISC_MARGIN_TOP;
    public const LEFT_PARAM = SystemParameter::PSV_DISC_MARGIN_LEFT;
    public const DEFAULT_TOP = 1128;
    public const DEFAULT_LEFT = 1633;
}
