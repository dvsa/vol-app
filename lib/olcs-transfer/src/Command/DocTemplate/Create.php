<?php

/**
 * Create document template
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Transfer\Command\DocTemplate;

use Dvsa\Olcs\Transfer\FieldType\Traits\Category;
use Dvsa\Olcs\Transfer\FieldType\Traits\Description;
use Dvsa\Olcs\Transfer\FieldType\Traits\FilenameAndContent;
use Dvsa\Olcs\Transfer\FieldType\Traits\IsNiOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\LetterTypeOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\SubCategoryOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\SuppressFromOp;
use Dvsa\Olcs\Transfer\FieldType\Traits\TemplateFolder;
use Dvsa\Olcs\Transfer\FieldType\Traits\TemplateSlugOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/doc-template")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    use TemplateFolder;
    use Category;
    use SubCategoryOptional;
    use Description;
    use FilenameAndContent;
    use SuppressFromOp;
    use IsNiOptional;
    use TemplateSlugOptional;
    use LetterTypeOptional;
}
