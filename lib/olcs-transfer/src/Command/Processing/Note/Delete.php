<?php

namespace Dvsa\Olcs\Transfer\Command\Processing\Note;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType as FieldType;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;

/**
 * Class to Delete a Note
 *
 * @Transfer\Method("DELETE")
 * @Transfer\RouteName("backend/processing/note/single")
 */
class Delete extends AbstractCommand implements FieldType\IdentityInterface
{
    // Identity & Locking
    use FieldTypeTraits\Identity;
}
