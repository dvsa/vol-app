<?php

namespace Dvsa\Olcs\Transfer\Command\TaskAllocationRule;

use Dvsa\Olcs\Transfer\FieldType\Traits\Category;
use Dvsa\Olcs\Transfer\FieldType\Traits\GoodsOrPsvOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IsMlhOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\SubCategoryOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\Team;
use Dvsa\Olcs\Transfer\FieldType\Traits\TrafficAreaOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\UserOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/task-allocation-rule")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    use Category;
    use SubCategoryOptional;
    use Team;
    use UserOptional;
    use GoodsOrPsvOptional;
    use IsMlhOptional;
    use TrafficAreaOptional;
}
