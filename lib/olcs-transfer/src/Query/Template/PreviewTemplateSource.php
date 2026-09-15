<?php

/**
 * Preview a template prior to saving
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Template;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\TemplateSource;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/template/preview-template-source")
 */
class PreviewTemplateSource extends AbstractQuery
{
    use Identity;
    use TemplateSource;
}
