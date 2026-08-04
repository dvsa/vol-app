<?php

namespace Dvsa\Olcs\Transfer\Query\Processing;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\FieldType;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;

/**
 * Class Note
 * @Transfer\RouteName("backend/processing/note")
 */
class NoteList extends AbstractQuery implements
    PagedQueryInterface,
    OrderedQueryInterface,
    FieldType\ApplicationInterface,
    FieldType\CasesInterface,
    FieldType\LicenceInterface,
    FieldType\OrganisationInterface,
    FieldType\TransportManagerInterface,
    FieldType\UserInterface
{
    use PagedTrait;
    use OrderedTrait;

    // Foreign Keys
    use FieldTypeTraits\ApplicationOptional;
    use FieldTypeTraits\BusRegOptional;
    use FieldTypeTraits\CasesOptional;
    use FieldTypeTraits\CasesMultiple;
    use FieldTypeTraits\LicenceOptional;
    use FieldTypeTraits\OrganisationOptional;
    use FieldTypeTraits\TransportManagerOptional;
    use FieldTypeTraits\UserOptional;
    use FieldTypeTraits\NoteTypeOptional;
}
