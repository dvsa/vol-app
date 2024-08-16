<?php

namespace Dvsa\Olcs\Api\Domain\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\CorrelationIdOptional;

/**
 * Create Document
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateDocumentSpecific extends AbstractCommand
{
    use CorrelationIdOptional;

    protected $filename;

    protected $identifier;

    protected $size;

    protected $application;

    protected $busReg;

    protected $case;

    protected $irfoOrganisation;

    protected $submission;

    protected $surrender;

    protected $trafficArea;

    protected $transportManager;

    protected $licence;

    protected $operatingCentre;

    protected $opposition;

    protected $continuationDetail;

    protected $category;

    protected $subCategory;

    protected $description;

    protected $isExternal;

    protected $isScan = 0;

    protected $isEbsrPack = 0;

    protected $issuedDate;

    protected $metadata;

    protected $user;

    protected $irhpApplication;

    protected $messagingConversation;

    protected $messagingMessage;

    protected $isPostSubmissionUpload = 0;

    /**
     * Get filename
     *
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get identifier
     *
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Get size
     *
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Get application
     *
     * @return mixed
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Get bus reg
     *
     * @return mixed
     */
    public function getBusReg()
    {
        return $this->busReg;
    }

    /**
     * Get case
     *
     * @return mixed
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * Get IRFO Organisation
     *
     * @return mixed
     */
    public function getIrfoOrganisation()
    {
        return $this->irfoOrganisation;
    }

    /**
     * Get submission
     *
     * @return mixed
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * Get traffic area
     *
     * @return mixed
     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }

    /**
     * Get transport manager
     *
     * @return mixed
     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * Get Licence
     *
     * @return mixed
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Get Operating Centre
     *
     * @return mixed
     */
    public function getOperatingCentre()
    {
        return $this->operatingCentre;
    }

    /**
     * Get Opposition
     *
     * @return mixed
     */
    public function getOpposition()
    {
        return $this->opposition;
    }

    /**
     * Get continuation detail
     *
     * @return int
     */
    public function getContinuationDetail()
    {
        return $this->opposition;
    }

    /**
     * Get category
     *
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Get sub category
     *
     * @return mixed
     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * Get description
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get is external
     *
     * @return mixed
     */
    public function getIsExternal()
    {
        return $this->isExternal;
    }

    /**
     * Get is scan
     *
     * @return mixed
     */
    public function getIsScan()
    {
        return $this->isScan;
    }

    /**
     * Get is EBSR Pack
     *
     * @return bool
     */
    public function getIsEbsrPack()
    {
        return $this->isEbsrPack;
    }

    /**
     * Get issued date
     *
     * @return mixed
     */
    public function getIssuedDate()
    {
        return $this->issuedDate;
    }

    /**
     * Get meta data
     *
     * @return mixed
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Get user
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get irhp application
     *
     * @return int
     */
    public function getIrhpApplication()
    {
        return $this->irhpApplication;
    }

    /**
     * @return int
     */
    public function getIsPostSubmissionUpload()
    {
        return $this->isPostSubmissionUpload;
    }

    public function getConversation()
    {
        return $this->messagingConversation;
    }

    public function getMessage()
    {
        return $this->messagingMessage;
    }
}
