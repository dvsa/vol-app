<?php

namespace Dvsa\Olcs\Transfer\Command;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType as FieldType;

/**
 * AbstractXmlCommand class
 */
abstract class AbstractXmlCommand extends AbstractCommand
{
    use FieldType\Traits\Xml;
}
