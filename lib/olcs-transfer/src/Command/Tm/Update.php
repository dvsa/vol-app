<?php

/**
 * Update Transport Manager
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Tm;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/transport-manager/single")
 * @Transfer\Method("PUT")
 */
final class Update extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $id;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $version;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $homeCdId;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $homeCdVersion;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $homeAddressId;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $homeAddressVersion;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $workAddressId;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $workAddressVersion;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $personId;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $personVersion;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":2,"max":35})
     */
    public $firstName;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":2,"max":35})
     */
    public $lastName;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\EmailAddress")
     * @Transfer\Optional
     */
    public $emailAddress;

    /**
     * @Transfer\Validator("Date",options={"format":"Y-m-d"})
     */
    public $birthDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":35})
     */
    public $birthPlace;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "title_dr","title_miss","title_mr","title_mrs","title_ms"
     *          }
     *      }
     * )
     */
    protected $title;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"tm_t_e", "tm_t_i", "tm_t_b"}})
     */
    protected $type;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "tm_s_cur","tm_s_dis","tm_s_rem"
     *          }
     *      }
     * )
     */
    protected $status;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":90})
     * @Transfer\Optional
     */
    public $homeAddressLine1;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":90})
     * @Transfer\Optional
     * @Transfer\Optional
     */
    public $homeAddressLine2;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":100})
     * @Transfer\Optional
     * @Transfer\Optional
     */
    public $homeAddressLine3;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":35})
     * @Transfer\Optional
     * @Transfer\Optional
     */
    public $homeAddressLine4;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":30})
     * @Transfer\Optional
     */
    public $homeTown;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":8})
     * @Transfer\Optional
     */
    public $homePostcode;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":2})
     * @Transfer\Optional
     */
    public $homeCountryCode;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":90})
     * @Transfer\Optional
     */
    public $workAddressLine1;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":90})
     * @Transfer\Optional
     */
    public $workAddressLine2;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":100})
     * @Transfer\Optional
     */
    public $workAddressLine3;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":35})
     * @Transfer\Optional
     */
    public $workAddressLine4;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":30})
     * @Transfer\Optional
     */
    public $workTown;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":8})
     * @Transfer\Optional
     */
    public $workPostcode;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":2})
     * @Transfer\Optional
     */
    public $workCountryCode;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":100})
     */
    public $nysiisForename;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":100})
     */
    public $nysiisFamilyname;

    public function getId()
    {
        return $this->id;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getHomeCdId()
    {
        return $this->homeCdId;
    }

    public function getHomeCdVersion()
    {
        return $this->homeCdVersion;
    }

    public function getHomeAddressId()
    {
        return $this->homeAddressId;
    }

    public function getHomeAddressVersion()
    {
        return $this->homeAddressVersion;
    }

    public function getWorkAddressId()
    {
        return $this->workAddressId;
    }

    public function getWorkAddressVersion()
    {
        return $this->workAddressVersion;
    }

    public function getPersonId()
    {
        return $this->personId;
    }

    public function getPersonVersion()
    {
        return $this->personVersion;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function getBirthDate()
    {
        return $this->birthDate;
    }

    public function getBirthPlace()
    {
        return $this->birthPlace;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getHomeAddressLine1()
    {
        return $this->homeAddressLine1;
    }

    public function getHomeAddressLine2()
    {
        return $this->homeAddressLine2;
    }

    public function getHomeAddressLine3()
    {
        return $this->homeAddressLine3;
    }

    public function getHomeAddressLine4()
    {
        return $this->homeAddressLine4;
    }

    public function getHomeTown()
    {
        return $this->homeTown;
    }

    public function getHomePostcode()
    {
        return $this->homePostcode;
    }

    public function getHomeCountryCode()
    {
        return $this->homeCountryCode;
    }

    public function getWorkAddressLine1()
    {
        return $this->workAddressLine1;
    }

    public function getWorkAddressLine2()
    {
        return $this->workAddressLine2;
    }

    public function getWorkAddressLine3()
    {
        return $this->workAddressLine3;
    }

    public function getWorkAddressLine4()
    {
        return $this->workAddressLine4;
    }

    public function getWorkTown()
    {
        return $this->workTown;
    }

    public function getWorkPostcode()
    {
        return $this->workPostcode;
    }

    public function getWorkCountryCode()
    {
        return $this->workCountryCode;
    }

    public function getNysiisForename()
    {
        return $this->nysiisForename;
    }

    public function getNysiisFamilyname()
    {
        return $this->nysiisFamilyname;
    }
}
