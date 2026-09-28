<?php

namespace Dvsa\Olcs\Transfer\Query\Letter\LetterInstance;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\CasesOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\BusRegOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\TransportManagerOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrfoOrganisationOptional;

/**
 * Goods/PSV and NI context of the entity a letter is being generated for
 *
 * @Transfer\RouteName("backend/letter/letter-instance/generation-context")
 */
final class GenerationContext extends AbstractQuery
{
    use LicenceOptional;
    use ApplicationOptional;
    use CasesOptional;
    use BusRegOptional;
    use TransportManagerOptional;
    use IrhpApplicationOptional;
    use IrfoOrganisationOptional;
}
