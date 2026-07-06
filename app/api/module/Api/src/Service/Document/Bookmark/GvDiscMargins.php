<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\System\SystemParameter;

/**
 * Page margins for GVDiscTemplateGotenberg (token: Gv_Disc_Margins).
 * Defaults calibrated against the known-good GV246 stationery scan.
 */
class GvDiscMargins extends AbstractDiscMargins
{
    public const TOP_PARAM = SystemParameter::GOODS_DISC_MARGIN_TOP;
    public const LEFT_PARAM = SystemParameter::GOODS_DISC_MARGIN_LEFT;
    public const DEFAULT_TOP = 1094;
    public const DEFAULT_LEFT = 1607;
}
