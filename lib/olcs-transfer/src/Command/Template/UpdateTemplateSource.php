<?php

/**
 * Update template source
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Template;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\TemplateSource;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/template/update-template-source")
 * @Transfer\Method("PUT")
 */
final class UpdateTemplateSource extends AbstractCommand
{
    use Identity;
    use TemplateSource;
}
