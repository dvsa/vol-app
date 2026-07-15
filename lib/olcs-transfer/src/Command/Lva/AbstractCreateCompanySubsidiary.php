<?php

namespace Dvsa\Olcs\Transfer\Command\Lva;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Save (Create/Update) Company Subsidiary
 *
 * @author Dmitry Golubev <dmitrijs.golubev@valtech.co.uk>
 */
abstract class AbstractCreateCompanySubsidiary extends AbstractCommand
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $name;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $companyNo;

    /**
     * Return Name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns company number
     *
     * @return string|null
     */
    public function getCompanyNo()
    {
        return $this->companyNo;
    }
}
