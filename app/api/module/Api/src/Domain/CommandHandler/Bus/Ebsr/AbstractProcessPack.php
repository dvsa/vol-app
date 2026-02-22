<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\BusRegistrationInputFactory;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\ProcessedDataInputFactory;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\ShortNoticeInputFactory;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\XmlStructureInputFactory;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueueCmd;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrErrors as SendEbsrErrorsCmd;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\SubmissionResult as SubmissionResultFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\RequestMap as RequestMapQueueCmd;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod as BusNoticePeriodEntity;
use Dvsa\Olcs\Api\Domain\Repository\BusServiceType as BusServiceTypeRepo;
use Dvsa\Olcs\Api\Entity\Bus\BusServiceType as BusServiceTypeEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Domain\Repository\TrafficArea as TrafficAreaRepo;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LocalAuthorityEntity;
use Dvsa\Olcs\Api\Domain\Repository\LocalAuthority as LocalAuthorityRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentLinks as UpdateDocumentLinksCmd;
use Dvsa\Olcs\Api\Domain\Command\Bus\CreateBusFee as CreateBusFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\CreateTxcInbox as CreateTxcInboxCmd;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrReceived as SendEbsrReceivedCmd;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrRefreshed as SendEbsrRefreshedCmd;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;
use Dvsa\Olcs\Api\Domain\FileProcessorAwareInterface;
use Dvsa\Olcs\Api\Domain\FileProcessorAwareTrait;
use Psr\Container\ContainerInterface;

abstract class AbstractProcessPack extends AbstractCommandHandler implements
    UploaderAwareInterface,
    FileProcessorAwareInterface,
    ConfigAwareInterface
{
    use QueueAwareTrait;
    use UploaderAwareTrait;
    use FileProcessorAwareTrait;
    use ConfigAwareTrait;

    public const TASK_DESC_REFRESH = 'Data refresh';
    public const TASK_DESC_NEW = 'New application';
    public const TASK_DESC_VAR = 'Variation';
    public const TASK_DESC_CANCEL = 'Cancellation';
    public const TASK_DESCRIPTION = '%s created: %s';

    protected $repoServiceName = 'Bus';

    protected $extraRepos = [
        'Document',
        'EbsrSubmission',
        'Licence',
        'BusRegOtherService',
        'TrafficArea',
        'LocalAuthority',
        'BusServiceType'
    ];

    protected $xmlStructureInput;

    protected $busRegInput;

    protected $processedDataInput;

    protected $shortNoticeInput;

    /**
     * @var SubmissionResultFilter
     */
    protected $submissionResultFilter;

    /**
     * @var Result
     */
    protected $result;

    /**
     * Calls the specified input filters/validators
     *
     * @param string               $filter  the filter being called
     * @param EbsrSubmissionEntity $ebsrSub ebsr submission entity
     * @param DocumentEntity       $doc     document entity
     * @param string               $xmlName name of the xml file
     * @param array                $value   input value
     * @param array                $context input context
     *
     * @return array|bool
     */
    protected function validateInput(
        $filter,
        EbsrSubmissionEntity $ebsrSub,
        DocumentEntity $doc,
        $xmlName,
        $value,
        $context = []
    ) {
        $inputFilter = $filter . 'Input';

        $this->$inputFilter->setValue($value);

        if (!$this->$inputFilter->isValid($context)) {
            //create error messages for use by the front end
            $messages = $this->$inputFilter->getMessages();

            //get input values we can use for debug
            $inputValue = $this->$inputFilter->getValue();

            //process the validation failure information
            $this->processFailure($ebsrSub, $doc, $messages, $xmlName, $inputValue);

            return false;
        }

        return $this->$inputFilter->getValue();
    }

    /**
     * Processes a failure
     * Sets submission to failed and queues error email
     *
     * @param EbsrSubmissionEntity $ebsrSub    EBSR submission entity
     * @param DocumentEntity       $doc        document entity
     * @param array                $messages   array of error messages
     * @param string               $xmlName    name of the xml file
     * @param mixed                $inputValue the input value
     *
     * @return void
     */
    protected function processFailure(
        EbsrSubmissionEntity $ebsrSub,
        DocumentEntity $doc,
        $messages,
        $xmlName,
        mixed $inputValue
    ) {
        $this->addErrorMessages($doc, $messages, $xmlName);

        //save submission result data, used for error messages in emails, and possible debugging later
        $resultData = $this->getSubmissionResultData($messages, $inputValue, $ebsrSub);
        $this->setEbsrSubmissionFailed($ebsrSub, $resultData);

        //trigger ebsr failure email for the user
        $this->handleSideEffect($this->getEbsrErrorEmailCmd($ebsrSub->getId()));
    }

    /**
     * Creates a serialized string consisting of error messages and input data, saved to ebsrSubmissionResult DB field
     *
     * @param array                $errorMessages array of error messages
     * @param mixed                $rawData       the raw data
     * @param EbsrSubmissionEntity $ebsrSub       ebsr submission entity
     *
     * @return array
     */
    protected function getSubmissionResultData(array $errorMessages, mixed $rawData, EbsrSubmissionEntity $ebsrSub)
    {
        $input = [
            'rawData' => $rawData,
            'errorMessages' => $errorMessages,
            'ebsrSub' => $ebsrSub
        ];

        return $this->submissionResultFilter->filter($input);
    }

    /**
     * Adds error messages to the result object
     *
     * @param DocumentEntity $doc      document entity
     * @param array          $messages array of error messages
     * @param string         $xmlName  name of the xml file
     *
     * @return Result
     */
    protected function addErrorMessages(DocumentEntity $doc, array $messages, $xmlName)
    {
        $filename = '';
        $joinedMessages = strtolower(implode(', ', $messages));

        if (!empty($xmlName)) {
            $filename = ' (' . basename($xmlName) . ')';
        }

        $errorMsg = 'Error with ' . $doc->getDescription() . $filename . ': ' . $joinedMessages . ' - not processed';
        $this->result->addId('error_messages', $errorMsg, true);

        return $this->result;
    }

    /**
     * Add in ebsr submission data after the file has been processed
     *
     * @param EbsrSubmissionEntity $ebsrSub  EBSR submission entity
     * @param array                $ebsrData array of EBSR data
     *
     * @return EbsrSubmissionEntity
     */
    protected function addXmlDataToEbsrSubmission(EbsrSubmissionEntity $ebsrSub, array $ebsrData)
    {
        $ebsrSub->setLicenceNo($ebsrData['licNo']);
        $ebsrSub->setVariationNo($ebsrData['variationNo']);
        $ebsrSub->setRegistrationNo($ebsrData['routeNo']);
        $ebsrSub->setOrganisationEmailAddress($ebsrData['organisationEmail']);
        $ebsrSub->setTxcVersion($ebsrData['txcSchemaVersion']);

        return $ebsrSub;
    }

    /**
     * Sets the EBSR submission to failed, and saves the record
     *
     * @param EbsrSubmissionEntity $ebsrSub        EBSR submission entity
     * @param string               $ebsrResultData serialized array of data
     *
     * @return EbsrSubmissionEntity
     */
    protected function setEbsrSubmissionFailed(EbsrSubmissionEntity $ebsrSub, $ebsrResultData)
    {
        $ebsrSub->finishValidating(
            $this->getRepo()->getRefdataReference(EbsrSubmissionEntity::FAILED_STATUS),
            $ebsrResultData
        );

        $this->getRepo('EbsrSubmission')->save($ebsrSub);
        return $ebsrSub;
    }

    /**
     * Creates the bus registration
     *
     * @param array              $ebsrData       array of EBSR data
     * @param BusRegEntity|array $previousBusReg information on the previous bus registration
     * @param LicenceEntity      $licence        licence
     *
     * @return BusRegEntity
     */
    protected function createBusReg(array $ebsrData, $previousBusReg, LicenceEntity $licence)
    {
        //decide what to do based on txcAppType
        $busReg = match ($ebsrData['txcAppType']) {
            BusRegEntity::TXC_APP_NEW => $this->createNew($ebsrData, $licence),
            BusRegEntity::TXC_APP_CANCEL => $this->createVariation($previousBusReg, BusRegEntity::STATUS_CANCEL),
            BusRegEntity::TXC_APP_NON_CHARGEABLE => $this->createVariation($previousBusReg, BusRegEntity::STATUS_REGISTERED),
            default => $this->createVariation($previousBusReg, BusRegEntity::STATUS_VAR),
        };

        $busReg->fromData($this->prepareBusRegData($ebsrData));
        $this->processServiceNumbers($busReg, $ebsrData['otherServiceNumbers']);

        /**
         * For most records we calculate short notice based on the dates.
         * For data refreshes, we only validate short notice if the operator includes the information
         */
        if ($ebsrData['txcAppType'] !== BusRegEntity::TXC_APP_NON_CHARGEABLE) {
            $busReg->populateShortNotice();
        } elseif (!empty($ebsrData['busShortNotice'])) {
            $busReg->setIsShortNotice('Y');
        }

        return $busReg;
    }

    /**
     * Unset any data keys that might clash with the busReg entity fromData method
     *
     * @param array $ebsrData array of EBSR data
     *
     * @return array
     */
    protected function prepareBusRegData($ebsrData)
    {
        $busRegData = $ebsrData;
        unset($busRegData['documents']);
        unset($busRegData['variationNo']);
        return $busRegData;
    }

    /**
     * Returns a list of side effects as a result of the EBSR submission success
     *
     * 1. Add supporting documents to the doc store
     * 2. Create TXC inbox record
     * 3. Create a task
     * 4. Queue Transxchange map request
     * 5. Create fee (optional)
     * 6. Queue confirmation email
     *
     * @param array                $ebsrData array of EBSR data
     * @param BusRegEntity         $busReg   bus reg entity
     * @param EbsrSubmissionEntity $ebsrSub  EBSR submission entity
     * @param string               $docPath  path to the documents being persisted
     *
     * @return array
     */
    protected function getSideEffects(array $ebsrData, BusRegEntity $busReg, EbsrSubmissionEntity $ebsrSub, $docPath)
    {
        $busRegId = $busReg->getId();
        $sideEffects = $this->persistDocuments($ebsrData, $busReg, $ebsrSub, $docPath);
        $sideEffects[] = $this->createTxcInboxCmd($busRegId);
        $sideEffects[] = $this->getRequestMapQueueCmd($busRegId);
        $sideEffects[] = $this->createTaskCmd($busReg);

        if ($busReg->isChargeableStatus()) {
            $sideEffects[] = CreateBusFeeCmd::create(['id' => $busRegId]);
        }

        if ($busReg->isEbsrRefresh()) {
            $sideEffects[] = $this->getEbsrRefreshedEmailCmd($ebsrSub->getId());
        } else {
            $sideEffects[] = $this->getEbsrReceivedEmailCmd($ebsrSub->getId());
        }

        return $sideEffects;
    }

    /**
     * Returns a side effect to save the supporting documents and schematic map to the doc store
     *
     * @param array                $ebsrData array of EBSR data
     * @param BusRegEntity         $busReg   bus reg entity
     * @param EbsrSubmissionEntity $ebsrSub  EBSR submission entity
     * @param string               $docPath  path to the documents being persisted
     *
     * @return array
     */
    protected function persistDocuments(array $ebsrData, BusRegEntity $busReg, EbsrSubmissionEntity $ebsrSub, $docPath)
    {
        //store any supporting documents
        if (isset($ebsrData['documents'])) {
            foreach ($ebsrData['documents'] as $docName) {
                $path = $docPath . '/' . $docName;
                $sideEffects[] = $this->persistSupportingDoc($path, $busReg, $docName, 'Supporting document');
            }
        }

        //store a new map if present
        if (isset($ebsrData['map'])) {
            $path = $docPath . '/' . $ebsrData['map'];
            $sideEffects[] = $this->persistSupportingDoc($path, $busReg, $ebsrData['map'], 'Schematic map');
        }

        //update the original zip file with links to the licence and bus registration
        $documentLinkData = [
            'id' => $ebsrSub->getDocument()->getId(),
            'busReg' => $busReg->getId(),
            'licence' => $busReg->getLicence()->getId()
        ];

        $sideEffects[] = UpdateDocumentLinksCmd::create($documentLinkData);

        return $sideEffects;
    }

    /**
     * Returns an upload command to add the supporting docs to the doc store
     *
     * @param string       $content     the document content
     * @param BusRegEntity $busReg      bus registration entity
     * @param string       $filename    document filename
     * @param string       $description document description
     *
     * @return UploadCmd
     */
    protected function persistSupportingDoc($content, BusRegEntity $busReg, $filename, $description)
    {
        $data = [
            'content' => base64_encode(file_get_contents($content)),
            'busReg' => $busReg->getId(),
            'licence' => $busReg->getLicence()->getId(),
            'category' => CategoryEntity::CATEGORY_BUS_REGISTRATION,
            'subCategory' => CategoryEntity::BUS_SUB_CATEGORY_OTHER_DOCUMENTS,
            'filename' => $filename,
            'description' => $description
        ];

        return UploadCmd::create($data);
    }

    /**
     * Returns a command to create a txc inbox record
     *
     * @param int $busRegId bus reg id
     *
     * @return CreateTxcInboxCmd
     */
    protected function createTxcInboxCmd($busRegId)
    {
        return CreateTxcInboxCmd::create(['id' => $busRegId]);
    }

    /**
     * Returns a command to queue a transxchange map request
     */
    protected function getRequestMapQueueCmd(int $busRegId): RequestMapQueueCmd
    {
        return RequestMapQueueCmd::create(['id' => $busRegId, 'scale' => 'auto']);
    }

    /**
     * Returns a command to queue a data refresh email
     *
     * @param int $ebsrId EBSR submission id
     *
     * @return CreateQueueCmd
     */
    protected function getEbsrRefreshedEmailCmd($ebsrId)
    {
        return $this->emailQueue(SendEbsrRefreshedCmd::class, ['id' => $ebsrId], $ebsrId);
    }

    /**
     * Returns a command to queue a received email
     *
     * @param int $ebsrId EBSR submission id
     *
     * @return CreateQueueCmd
     */
    protected function getEbsrReceivedEmailCmd($ebsrId)
    {
        return $this->emailQueue(SendEbsrReceivedCmd::class, ['id' => $ebsrId], $ebsrId);
    }

    /**
     * Returns a command to queue an error email
     *
     * @param int $ebsrId EBSR submission id
     *
     * @return CreateQueueCmd
     */
    protected function getEbsrErrorEmailCmd($ebsrId)
    {
        return $this->emailQueue(SendEbsrErrorsCmd::class, ['id' => $ebsrId], $ebsrId);
    }

    private function createTaskCmd(BusRegEntity $busReg): CreateTask
    {
        $appType = self::TASK_DESC_REFRESH;

        if (!$busReg->isEbsrRefresh()) {
            $status = $busReg->getStatus()->getId();

            $appType = match ($status) {
                BusRegEntity::STATUS_CANCEL => self::TASK_DESC_CANCEL,
                BusRegEntity::STATUS_VAR => self::TASK_DESC_VAR,
                default => self::TASK_DESC_NEW,
            };
        }

        $data = [
            'category' => Task::CATEGORY_BUS,
            'subCategory' => Task::SUBCATEGORY_EBSR,
            'description' => sprintf(self::TASK_DESCRIPTION, $appType, $busReg->getRegNo()),
            'actionDate' => date('Y-m-d'),
            'busReg' => $busReg->getId(),
            'licence' => $busReg->getLicence()->getId(),
        ];

        return CreateTask::create($data);
    }

    /**
     * Create a new bus reg
     *
     * @param array         $ebsrData array of EBSR data
     * @param LicenceEntity $licence  licence
     *
     * @throws Exception\ForbiddenException
     * @return BusRegEntity
     */
    protected function createNew(array $ebsrData, LicenceEntity $licence)
    {
        $refDataStatus = $this->getRepo()->getRefdataReference(BusRegEntity::STATUS_NEW);

        $newBusReg = BusRegEntity::createNew(
            $licence,
            $refDataStatus,
            $refDataStatus,
            $ebsrData['subsidised'],
            $ebsrData['busNoticePeriod']
        );

        //quick fix: overwrite the reg no that createNew produced, with the one from EBSR - need to move this logic
        $newBusReg->setRegNo($licence->getLicNo() . '/' . $ebsrData['routeNo']);

        return $newBusReg;
    }

    /**
     * Creates a bus reg variation
     *
     * @param BusRegEntity $busReg bus reg entity
     * @param string       $status status ref data key
     *
     * @return BusRegEntity
     */
    protected function createVariation(BusRegEntity $busReg, $status)
    {
        $refDataStatus = $this->getRepo()->getRefdataReference($status);
        return $busReg->createVariation($refDataStatus, $refDataStatus);
    }

    /**
     * Ebsr information which couldn't be processed using the pre-migration filters, as we needed Doctrine
     *
     * @param array $ebsrData array of EBSR data
     *
     * @return array
     */
    protected function getDoctrineInformation(array $ebsrData)
    {
        // Split lineNames into serviceNo and otherServiceNumbers
        if (!isset($ebsrData['lineNames']) || !is_array($ebsrData['lineNames']) || empty($ebsrData['lineNames'])) {
            throw new Exception\ValidationException(['lineNames' => 'At least one LineName is required in the TransXChange file']);
        }

        $ebsrData['serviceNo'] = $ebsrData['lineNames'][0];
        $ebsrData['otherServiceNumbers'] = array_slice($ebsrData['lineNames'], 1) ?: [];
        unset($ebsrData['lineNames']);

        $ebsrData['subsidised'] = $this->getRepo()->getRefdataReference($ebsrData['subsidised']);
        $ebsrData['naptanAuthorities'] = $this->processNaptan($ebsrData['naptan']);
        $ebsrData['localAuthoritys'] = $this->processLocalAuthority($ebsrData['localAuthorities']);
        $ebsrData['trafficAreas'] = $this->processTrafficAreas($ebsrData['trafficAreas'], $ebsrData['localAuthoritys']);
        $ebsrData['busServiceTypes'] = $this->processServiceTypes($ebsrData['serviceClassifications']);
        $ebsrData['busNoticePeriod'] = $this->getRepo()->getReference(
            BusNoticePeriodEntity::class,
            $ebsrData['busNoticePeriod']
        );

        return $ebsrData;
    }

    /**
     * Returns collection of service types.
     *
     * @param array $serviceTypes array of service types
     *
     * @return ArrayCollection
     */
    protected function processServiceTypes(array $serviceTypes)
    {
        $collection = new ArrayCollection();

        if (!empty($serviceTypes)) {
            /** @var BusServiceTypeRepo $repo */
            $repo = $this->getRepo('BusServiceType');
            $serviceTypeArray = array_keys($serviceTypes);

            $serviceTypeList = $repo->fetchByTxcName($serviceTypeArray);

            /** @var BusServiceTypeEntity $serviceType */
            foreach ($serviceTypeList as $serviceType) {
                $collection->add($serviceType);
            }
        }

        return $collection;
    }

    /**
     * Processes additional service numbers
     *
     * @param BusRegEntity $busReg         bus reg entity
     * @param array        $serviceNumbers array of service numbers
     *
     * @return BusRegEntity
     */
    protected function processServiceNumbers(BusRegEntity $busReg, array $serviceNumbers)
    {
        //first make sure we have an empty array collection
        $busReg->setOtherServices(new ArrayCollection());

        foreach ($serviceNumbers as $number) {
            $busReg->addOtherServiceNumber($number);
        }

        return $busReg;
    }

    /**
     * Returns collection of local authorities.
     *
     * @param array $localAuthority array of local authorities
     *
     * @return ArrayCollection
     */
    protected function processLocalAuthority(array $localAuthority)
    {
        $collection = new ArrayCollection();

        if (!empty($localAuthority)) {
            /** @var LocalAuthorityRepo $repo */
            $repo = $this->getRepo('LocalAuthority');
            $laList = $repo->fetchByTxcName($localAuthority);

            /** @var LocalAuthorityEntity $la */
            foreach ($laList as $la) {
                $collection->add($la);
            }
        }

        return $collection;
    }

    /**
     * Returns collection of local authorities based on the naptan codes.
     *
     * @param array $naptan array of naptan codes
     *
     * @return ArrayCollection
     */
    protected function processNaptan(array $naptan)
    {
        $collection = new ArrayCollection();

        if (!empty($naptan)) {
            /** @var LocalAuthorityRepo $repo */
            $repo = $this->getRepo('LocalAuthority');
            $laList = $repo->fetchByNaptan($naptan);

            /** @var LocalAuthorityEntity $la */
            foreach ($laList as $la) {
                $collection->add($la);
            }
        }

        return $collection;
    }

    /**
     * Returns collection of traffic areas.
     *
     * @param array           $trafficAreas     array of traffic areas
     * @param ArrayCollection $localAuthorities collections of local authorities
     *
     * @return ArrayCollection
     */
    protected function processTrafficAreas(array $trafficAreas, ArrayCollection $localAuthorities)
    {
        $collection = new ArrayCollection();

        if (!empty($trafficAreas)) {
            /** @var TrafficAreaRepo $repo */
            $repo = $this->getRepo('TrafficArea');
            $taList = $repo->fetchByTxcName($trafficAreas);

            /** @var TrafficAreaEntity $ta */
            foreach ($taList as $ta) {
                $collection->add($ta);
            }
        }

        /**
         * @var LocalAuthorityEntity $la
         */
        foreach ($localAuthorities as $la) {
            $ta = $la->getTrafficArea();

            if (!$collection->contains($ta)) {
                $collection->add($ta);
            }
        }

        return $collection;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->xmlStructureInput = $container->get(XmlStructureInputFactory::class);
        $this->busRegInput = $container->get(BusRegistrationInputFactory::class);
        $this->processedDataInput = $container->get(ProcessedDataInputFactory::class);
        $this->shortNoticeInput = $container->get(ShortNoticeInputFactory::class);
        $this->submissionResultFilter = $container->get('FilterManager')->get(SubmissionResultFilter::class);
        $this->result = new Result();
        return parent::__invoke($container, $requestedName, $options);
    }
}
