<?php

namespace Dvsa\Olcs\Transfer\Command\Surrender;

use Dvsa\Olcs\Transfer\FieldType\Traits;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/licence/single/surrender")
 * @Transfer\Method("PUT")
 */
class Update extends AbstractCommand
{
    use Traits\Identity;
    use Traits\Version;

     /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\LicenceDocumentStatus")
     * @Transfer\Optional
     */
    protected $communityLicenceDocumentStatus;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":500})
     * @Transfer\Optional
     */
    protected $communityLicenceDocumentInfo;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $digitalSignature;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0, "inclusive": true})
     * @Transfer\Optional
     */
    protected $discDestroyed;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0, "inclusive": true})
     * @Transfer\Optional
     */
    protected $discLost;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1})
     * @Transfer\Optional
     */
    protected $discLostInfo;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0, "inclusive": true})
     * @Transfer\Optional
     */
    protected $discStolen;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1})
     * @Transfer\Optional
     */
    protected $discStolenInfo;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\LicenceDocumentStatus")
     * @Transfer\Optional
     */
    protected $licenceDocumentStatus;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":500})
     * @Transfer\Optional
     */
    protected $licenceDocumentInfo;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\SurrenderStatus")
     * @Transfer\Optional
     */
    protected $status;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1})
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"sig_physical_signature","sig_digital_signature","sig_signature_not_required"}})
     * @Transfer\Optional
     */
    protected $signatureType;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 1})
     * @Transfer\Optional
     */
    protected $signatureChecked;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 1})
     * @Transfer\Optional
     */
    protected $ecmsChecked;

    /**
     * @return mixed
     */
    public function getSignatureChecked()
    {
        return $this->signatureChecked;
    }

    /**
     * @return mixed
     */
    public function getEcmsChecked()
    {
        return $this->ecmsChecked;
    }

    public function getCommunityLicenceDocumentStatus()
    {
        return $this->communityLicenceDocumentStatus;
    }

    public function getDigitalSignature()
    {
        return $this->digitalSignature;
    }

    public function getDiscDestroyed()
    {
        return $this->discDestroyed;
    }

    public function getDiscLost()
    {
        return $this->discLost;
    }

    public function getDiscLostInfo()
    {
        return $this->discLostInfo;
    }

    public function getDiscStolen()
    {
        return $this->discStolen;
    }

    public function getDiscStolenInfo()
    {
        return $this->discStolenInfo;
    }

    public function getLicenceDocumentStatus()
    {
        return $this->licenceDocumentStatus;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getSignatureType()
    {
        return $this->signatureType;
    }

    /**
     * @return mixed
     */
    public function getCommunityLicenceDocumentInfo()
    {
        return $this->communityLicenceDocumentInfo;
    }

    /**
     * @return mixed
     */
    public function getLicenceDocumentInfo()
    {
        return $this->licenceDocumentInfo;
    }
}
