<?php

/**
 * Update Translation Key
 */

namespace Dvsa\Olcs\Transfer\Command\TranslationKey;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\TranslationsArray;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/translation-key/single")
 * @Transfer\Method("PUT")
 */
final class Update extends AbstractCommand
{
    use Identity;
    use TranslationsArray;
}
