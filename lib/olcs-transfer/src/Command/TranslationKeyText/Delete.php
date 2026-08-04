<?php

/**
 * Delete Translation Key Text
 */

namespace Dvsa\Olcs\Transfer\Command\TranslationKeyText;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/translation-key-text/single")
 * @Transfer\Method("DELETE")
 */
final class Delete extends AbstractCommand
{
    use Identity;
}
