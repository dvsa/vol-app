<?php

namespace Dvsa\Olcs\Transfer\Query\Processing;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;

/**
 * Class Note
 * @Transfer\RouteName("backend/processing/note/single")
 */
class Note extends AbstractQuery implements FieldType\IdentityInterface
{
    use FieldTypeTraits\Identity;
}
