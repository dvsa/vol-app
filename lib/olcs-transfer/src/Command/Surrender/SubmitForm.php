<?php

namespace Dvsa\Olcs\Transfer\Command\Surrender;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\Version;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class GetStatus
 *
 * @package Dvsa\Olcs\Transfer\Command\Surrender
 * @Transfer\RouteName("backend/licence/single/surrender/submit-form")
 * @Transfer\Method("POST")
 */
class SubmitForm extends AbstractCommand
{
    use Identity;
    use Version;
}
