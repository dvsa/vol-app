<?php

/**
 * Create TmQualification
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\TmQualification;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/tm-qualification/create")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "tm_qt_ar", "tm_qt_cpcsi", "tm_qt_cpcsn", "tm_qt_exsi", "tm_qt_exsn", "tm_qt_niar",
     *              "tm_qt_nicpcsi", "tm_qt_nicpcsn", "tm_qt_niexsi", "tm_qt_niexsn", "tm_qt_lgvar", "tm_qt_nilgvar"
     *          }
     *      }
     * )
     */
    protected $qualificationType;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":50})
     * @Transfer\Optional
     */
    protected $serialNo;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $issuedDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":2})
     */
    public $countryCode;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $transportManager;

    public function getQualificationType()
    {
        return $this->qualificationType;
    }

    public function getSerialNo()
    {
        return $this->serialNo;
    }

    public function getIssuedDate()
    {
        return $this->issuedDate;
    }

    public function getCountryCode()
    {
        return $this->countryCode;
    }

    public function getTransportManager()
    {
        return $this->transportManager;
    }
}
