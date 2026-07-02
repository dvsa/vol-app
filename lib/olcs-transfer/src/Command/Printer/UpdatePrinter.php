<?php

/**
 * Create Printer
 */

namespace Dvsa\Olcs\Transfer\Command\Printer;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\Version;

/**
 * @Transfer\RouteName("backend/printer/single")
 * @Transfer\Method("PUT")
 */
final class UpdatePrinter extends AbstractCommand
{
    use Identity;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":45})
     */
    protected $printerName;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":255})
     * @Transfer\Optional
     */
    protected $description;

    public function getPrinterName()
    {
        return $this->printerName;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
