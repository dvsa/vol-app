<?php

/**
 * Generate translation key cache
 */

namespace Dvsa\Olcs\Transfer\Command\TranslationKey;

use Dvsa\Olcs\Transfer\FieldType\Traits\IdentityString;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\TranslationsArray;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/translation-key/generate-cache")
 * @Transfer\Method("POST")
 */
final class GenerateCache extends AbstractCommand
{
}
