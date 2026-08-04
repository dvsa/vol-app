<?php

/**
 * Upload report
 */

namespace Dvsa\Olcs\Transfer\Command\Report;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Command\LoggerOmitContentInterface;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/report/upload")
 * @Transfer\Method("POST")
 */
final class Upload extends AbstractCommand implements LoggerOmitContentInterface
{
    use FieldTypeTraits\ReportType;
    use FieldTypeTraits\FilenameAndContent;
    use FieldTypeTraits\TemplateSlugOptional;
    use FieldTypeTraits\TemplateNameOptional;
}
