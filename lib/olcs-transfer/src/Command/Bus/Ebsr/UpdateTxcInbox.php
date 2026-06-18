<?php

/**
 * Update txc inbox
 */

namespace Dvsa\Olcs\Transfer\Command\Bus\Ebsr;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/txc-inbox")
 * @Transfer\Method("PUT")
 */
final class UpdateTxcInbox extends AbstractCommand
{
    use FieldType\Ids;
}
