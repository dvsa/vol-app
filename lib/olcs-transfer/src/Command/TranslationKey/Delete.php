<?php

/**
 * Delete Translation Key
 */

namespace Dvsa\Olcs\Transfer\Command\TranslationKey;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/translation-key/single")
 * @Transfer\Method("DELETE")
 */
final class Delete extends AbstractCommand
{
    use Identity;
}
