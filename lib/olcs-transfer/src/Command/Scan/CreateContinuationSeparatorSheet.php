<?php

/**
 * CreateContinuationSeparatorSheet
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Scan;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/scan/continuation-separator-sheet")
 * @Transfer\Method("POST")
 */
final class CreateContinuationSeparatorSheet extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $licNo;

    /**
     * Get LicNo eg OB1234567
     *
     * @return string
     */
    public function getLicNo()
    {
        return $this->licNo;
    }
}
