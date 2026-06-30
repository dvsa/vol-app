<?php

/**
 * Update document template
 *
 * author Andy Newton <andy@the-shed.eu>
 */

namespace Dvsa\Olcs\Transfer\Command\DocTemplate;

use Dvsa\Olcs\Transfer\FieldType\Traits\Category;
use Dvsa\Olcs\Transfer\FieldType\Traits\Description;
use Dvsa\Olcs\Transfer\FieldType\Traits\FilenameAndContentOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IsNiOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\LetterTypeOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\SubCategoryOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\SuppressFromOp;
use Dvsa\Olcs\Transfer\FieldType\Traits\TemplateFolder;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/doc-template/single")
 * @Transfer\Method("POST")
 */
final class Update extends AbstractCommand
{
    use Identity;
    use TemplateFolder;
    use Category;
    use SubCategoryOptional;
    use Description;
    use FilenameAndContentOptional;
    use SuppressFromOp;
    use IsNiOptional;
    use LetterTypeOptional;
}
