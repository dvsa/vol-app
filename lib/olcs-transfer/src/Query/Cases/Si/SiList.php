<?php

/**
 * Serious Infringement List
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Cases\Si;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/case-si")
 */
class SiList extends AbstractQuery
{
    use \Dvsa\Olcs\Transfer\FieldType\Traits\Cases;
}
