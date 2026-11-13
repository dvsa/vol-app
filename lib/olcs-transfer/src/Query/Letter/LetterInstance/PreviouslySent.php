<?php

namespace Dvsa\Olcs\Transfer\Query\Letter\LetterInstance;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\FieldType\Traits\LetterType;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\CasesOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\BusRegOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\TransportManagerOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrfoOrganisationOptional;

/**
 * The letters of one type most recently sent for the entity a new letter is being started on,
 * with the issues each one covered
 */
#[Transfer\RouteName('backend/letter/letter-instance/previously-sent')]
final class PreviouslySent extends AbstractQuery
{
    use LetterType;
    use LicenceOptional;
    use ApplicationOptional;
    use CasesOptional;
    use BusRegOptional;
    use TransportManagerOptional;
    use IrhpApplicationOptional;
    use IrfoOrganisationOptional;
}
