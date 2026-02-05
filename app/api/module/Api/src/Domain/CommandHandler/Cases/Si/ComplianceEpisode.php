<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Exception\Exception;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Si\ErruRequestFailure;
use Dvsa\Olcs\Api\Service\Nr\Mapping\ComplianceEpisodeXml;
use Dvsa\Olcs\Api\Service\InputFilter\Input as InputFilter;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SiEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategory as SiCategoryEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyErruRequested as PenaltyRequestedEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyErruImposed as PenaltyImposedEntity;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest as ErruRequestEntity;
use Dvsa\Olcs\Api\Domain\Repository\SiCategoryType as SiCategoryTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\SiPenaltyImposedType as SiPenaltyImposedTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\SiPenaltyRequestedType as SiPenaltyRequestedTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\ErruRequest as ErruRequestRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Country as CountryRepo;
use Dvsa\Olcs\Api\Domain\Repository\ErruRequestFailure as ErruRequestFailureRepo;
use Dvsa\Olcs\Api\Domain\Command\Cases\Si\ComplianceEpisode as ComplianceEpisodeCmd;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentLinks as UpdateDocLinksCmd;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Domain\Command\Email\SendErruErrors as SendErrorEmailCmd;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueueCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Psr\Container\ContainerInterface;

final class ComplianceEpisode extends AbstractCommandHandler implements TransactionedInterface, UploaderAwareInterface
{
    use UploaderAwareTrait;
    use QueueAwareTrait;

    public const string MISSING_SI_CATEGORY_ERROR = 'Si category %s is not valid';
    public const string MISSING_IMPOSED_PENALTY_ERROR = 'Imposed penalty %s is not valid';
    public const string MISSING_REQUESTED_PENALTY_ERROR = 'Requested penalty %s is not valid';
    public const string MISSING_MEMBER_STATE_ERROR = 'Member state %s not found';
    public const string WORKFLOW_ID_EXISTS = 'Erru request with workflow id %s already exists';

    protected $repoServiceName = 'Cases';

    protected $extraRepos = [
        'Licence',
        'Country',
        'SiCategory',
        'SiCategoryType',
        'SiPenaltyRequestedType',
        'SiPenaltyImposedType',
        'ErruRequest',
        'ErruRequestFailure',
        'Document'
    ];

    /** @var InputFilter */
    protected $xmlStructureInput;

    /** @var InputFilter */
    protected $complianceEpisodeInput;

    /** @var InputFilter */
    protected $seriousInfringementInput;

    /** @var  ComplianceEpisodeXml */
    protected $xmlMapping;

    /**
     * si category doctrine information
     *
     * @var array
     */
    protected $siCategory = [];

    /**
     * requested erru penalty doctrine information
     *
     * @var array
     */
    protected $requestedPen = [];

    /**
     * imposed erru penalty doctrine information
     *
     * @var array
     */
    protected $imposedPen = [];

    /**
     * category type doctrine information
     *
     * @var array
     */
    protected $siCategoryType = [];

    /**
     * common data which will be standard across each infringement
     *
     * @var array
     */
    protected $commonData = [];

    /**
     * array of errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * The erru request document
     *
     * @var Document
     */
    protected $requestDocument;

    /**
     * @var Result
     */
    protected $result;

    /**
     * Handle command to create erru compliance episode
     *
     * @param CommandInterface|ComplianceEpisodeCmd $command the command
     *
     * @return Result
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var \DOMDocument $xmlDomDocument
         * @var Document $document
         * @var array $erruData
         */

        //result object with error flag set to false
        $this->result = new Result();
        $this->result->setFlag('hasErrors', false);

        $this->requestDocument = $this->getRepo('Document')->fetchUsingId($command);

        /** @var File $xmlFile */
        $xmlFile = $this->getUploader()->download($this->requestDocument->getIdentifier());

        // parse into a dom document, or return errors on failure
        if (!$xmlDomDocument = $this->validateInput('xmlStructure', $xmlFile->getContent(), [])) {
            return $this->result;
        }

        $parsedXmlData = $this->xmlMapping->mapData($xmlDomDocument);

        //extract the data we need from the dom document, on failure return a result object containing the errors
        if (!$erruData = $this->validateInput('complianceEpisode', $parsedXmlData)) {
            return $this->result;
        }

        //fetch doctrine data we use more than once (licence, member state etc.), return errors on failure
        if (!$this->getCommonData($erruData)) {
            return $this->result;
        }

        //generate a case object
        $case = $this->generateCase($erruData, $this->requestDocument);

        $checkDate = new \DateTime($erruData['checkDate']);
        $checkDate->setTime(0, 0);

        //there can be more than one serious infringement per request
        foreach ($erruData['si'] as $si) {
            //format/validate si data, on failure return a result object containing the errors
            if (!$si = $this->validateInput('seriousInfringement', $si)) {
                return $this->result;
            }

            //doctrine penalty data for this si (we may have this already from a previous si)
            $this->addDoctrinePenaltyData($si['imposedErrus'], $si['requestedErrus']);
            $this->addDoctrineCategoryTypeData($si['siCategoryType']);

            //we may have multiple errors from looking up penalty and category data in doctrine
            if (!empty($this->errors)) {
                $this->handleErrors($erruData, $this->errors);
                return $this->result;
            }

            $case->getSeriousInfringements()->add($this->getSi($case, $si, $checkDate));
        }

        $this->getRepo()->save($case);
        $this->result->merge(
            $this->handleSideEffects(
                [
                    $this->createTaskCmd($case),
                    $this->createUpdateDocLinksCmd($this->requestDocument, $case, $this->commonData['licence'])
                ]
            )
        );
        $this->result->addId('case', $case->getId());

        return $this->result;
    }

    private function getSi(CaseEntity $case, array $si, \DateTime $checkDate): SiEntity
    {
        $siEntity = new SiEntity(
            $case,
            $checkDate,
            $si['infringementDate'],
            $this->getRepo('SiCategory')->fetchById(SiCategoryEntity::ERRU_DEFAULT_CATEGORY),
            $this->siCategoryType[$si['siCategoryType']]
        );

        $siEntity->addImposedErrus($this->getImposedErruCollection($siEntity, $si['imposedErrus']));
        $siEntity->addRequestedErrus($this->getRequestedErruCollection($siEntity, $si['requestedErrus']));

        return $siEntity;
    }

    private function getImposedErruCollection(SiEntity $si, array $imposedErrus): ArrayCollection
    {
        $imposedErruCollection = new ArrayCollection();

        foreach ($imposedErrus as $imposedErru) {
            $imposedEntity = new PenaltyImposedEntity(
                $si,
                $this->imposedPen['siPenaltyImposedType'][$imposedErru['siPenaltyImposedType']],
                $this->imposedPen['executed'][$imposedErru['executed']],
                $imposedErru['startDate'],
                $imposedErru['endDate'],
                $imposedErru['finalDecisionDate'],
                $imposedErru['penaltyImposedIdentifier']
            );

            $imposedErruCollection->add($imposedEntity);
        }

        return $imposedErruCollection;
    }

    private function getRequestedErruCollection(SiEntity $si, array $requestedErrus): ArrayCollection
    {
        $requestedErruCollection = new ArrayCollection();

        foreach ($requestedErrus as $requestedErru) {
            $penalty = $this->requestedPen['siPenaltyRequestedType'][$requestedErru['siPenaltyRequestedType']];
            $requestedErruCollection->add(
                new PenaltyRequestedEntity(
                    $si,
                    $penalty,
                    $requestedErru['duration'],
                    $requestedErru['penaltyRequestedIdentifier']
                )
            );
        }

        return $requestedErruCollection;
    }

    private function generateCase(array $erruData, Document $requestDocument): CaseEntity
    {
        $case = new CaseEntity(
            new \DateTime(),
            $this->getRepo()->getRefdataReference(CaseEntity::LICENCE_CASE_TYPE),
            $this->getCaseCategories(),
            new ArrayCollection(),
            null,
            $this->commonData['licence'],
            null,
            null,
            'ERRU case automatically created'
        );

        $erruRequest = $this->getErruRequest(
            $case,
            $requestDocument,
            $erruData['originatingAuthority'],
            $erruData['transportUndertakingName'],
            $erruData['vrm'],
            $erruData['communityLicenceNumber'],
        );

        $case->setErruRequest($erruRequest);

        return $case;
    }

    private function getErruRequest(
        CaseEntity $case,
        Document $requestDocument,
        string $originatingAuthority,
        string $transportUndertakingName,
        string $vrm,
        string $communityLicenceNumber
    ): ErruRequestEntity {
        return new ErruRequestEntity(
            $case,
            $this->getRepo()->getRefdataReference(ErruRequestEntity::DEFAULT_CASE_TYPE),
            $this->commonData['memberState'],
            $requestDocument,
            $this->getRepo()->getRefdataReference(CommunityLic::STATUS_ACTIVE),
            $communityLicenceNumber,
            $this->commonData['totAuthVehicles'],
            $originatingAuthority,
            $transportUndertakingName,
            $vrm,
            $this->commonData['notificationNumber'],
            $this->commonData['workflowId']
        );
    }

    private function getCaseCategories(): ArrayCollection
    {
        return new ArrayCollection([$this->getRepo()->getRefdataReference(CaseEntity::ERRU_DEFAULT_CASE_CATEGORY)]);
    }

    /**
     * Gets doctrine category type data for each serious infringement, if we've already retrieved the data previously,
     * we don't do so again
     */
    private function addDoctrineCategoryTypeData(int $categoryType): void
    {
        if (!isset($this->siCategoryType[$categoryType])) {
            try {
                /** @var SiCategoryTypeRepo $categoryTypeRepo */
                $categoryTypeRepo = $this->getRepo('SiCategoryType');
                $this->siCategoryType[$categoryType] = $categoryTypeRepo->fetchById($categoryType);
            } catch (NotFoundException) {
                $this->errors[] = sprintf(self::MISSING_SI_CATEGORY_ERROR, $categoryType);
            }
        }
    }

    /**
     * Gets doctrine penalty data for each serious infringement, if we've already retrieved the data previously,
     * we don't do so again
     */
    private function addDoctrinePenaltyData(array $imposedErruData, array $requestedErruData): void
    {
        /**
         * @var SiPenaltyRequestedTypeRepo $imposedRepo
         * @var SiPenaltyImposedTypeRepo $requestedRepo
         */
        $imposedRepo = $this->getRepo('SiPenaltyImposedType');
        $requestedRepo = $this->getRepo('SiPenaltyRequestedType');
        $executedKey = 'executed';
        $imposedKey = 'siPenaltyImposedType';
        $requestedKey = 'siPenaltyRequestedType';

        foreach ($imposedErruData as $imposedErru) {
            //doctrine entity data for executed RefData
            $executedValue = $imposedErru[$executedKey];

            if (!isset($this->imposedPen[$executedKey][$executedValue])) {
                $this->imposedPen[$executedKey][$executedValue] = $this->getRepo()->getRefdataReference($executedValue);
            }

            //doctrine data for siPenaltyImposedType
            $imposedValue = $imposedErru[$imposedKey];

            if (!isset($this->imposedPen[$imposedKey][$imposedValue])) {
                try {
                    $this->imposedPen[$imposedKey][$imposedValue] = $imposedRepo->fetchById($imposedValue);
                } catch (NotFoundException) {
                    $this->errors[] = sprintf(self::MISSING_IMPOSED_PENALTY_ERROR, $imposedValue);
                }
            }
        }

        foreach ($requestedErruData as $requestedErru) {
            //doctrine data for siPenaltyRequestedType
            $requestedValue = $requestedErru[$requestedKey];

            if (!isset($this->requestedPen[$requestedKey][$requestedValue])) {
                try {
                    $this->requestedPen[$requestedKey][$requestedValue] = $requestedRepo->fetchById($requestedValue);
                } catch (NotFoundException) {
                    $this->errors[] = sprintf(self::MISSING_REQUESTED_PENALTY_ERROR, $requestedValue);
                }
            }
        }
    }

    /**
     * Erru information which couldn't be processed using the pre-migration filters, as we needed Doctrine.
     * This is common information that can be used on all serious infringements in the request.
     *
     * @param array $erruData array of erru data
     *
     * @throws NotFoundException
     * @throws Exception
     * @return array
     */
    private function getCommonData(array $erruData): array|false
    {
        /**
         * @var ErruRequestRepo $erruRequestRepo
         * @var LicenceRepo $licenceRepo
         * @var CountryRepo $countryRepo
         */
        $erruRequestRepo = $this->getRepo('ErruRequest');
        $licenceRepo = $this->getRepo('Licence');
        $countryRepo = $this->getRepo('Country');

        //check we don't already have an erru request with this workflow id
        if ($erruRequestRepo->existsByWorkflowId($erruData['workflowId'])) {
            $this->errors[] = sprintf(self::WORKFLOW_ID_EXISTS, $erruData['workflowId']);
        }

        try {
            $memberState = $countryRepo->fetchById($erruData['memberStateCode']);
        } catch (NotFoundException $e) {
            $this->errors[] = sprintf(self::MISSING_MEMBER_STATE_ERROR, $erruData['memberStateCode']);
        }

        try {
            $licence = $licenceRepo->fetchByLicNoWithoutAdditionalData($erruData['licenceNumber']);
        } catch (NotFoundException $e) {
            $this->errors[] = $e->getMessages()[0];
        }

        if (!empty($this->errors)) {
            return $this->handleErrors($erruData, $this->errors);
        }

        $this->commonData = [
            'licence' => $licence,
            'totAuthVehicles' => $licence->getTotAuthVehicles(),
            'memberState' => $memberState,
            'notificationNumber' => $erruData['notificationNumber'],
            'workflowId' => $erruData['workflowId'],
        ];

        return $this->commonData;
    }

    /**
     * Places errors into a result object, which can be
     *
     * @param array|string $input  input data, will be array so long as we managed to parse the XML initially
     * @param array        $errors the errors that were produced
     *
     * @return bool
     */
    private function handleErrors($input, array $errors): false
    {
        $this->errors = $errors;
        $this->result->setFlag('hasErrors', true);

        $requestFailure = new ErruRequestFailure($this->requestDocument, $errors, $input);

        /** @var ErruRequestFailureRepo $repo */
        $repo = $this->getRepo('ErruRequestFailure');
        $repo->save($requestFailure);

        $this->result->merge(
            $this->handleSideEffect(
                $this->createErrorEmailCmd($requestFailure->getId())
            )
        );

        return false;
    }

    /**
     * Validates the input
     *
     * @param string $filter  filter bring called
     * @param mixed  $value   input value
     * @param array  $context input context
     *
     * @throws Exception
     * @return mixed
     */
    private function validateInput($filter, mixed $value, $context = [])
    {
        $inputFilter = $filter . 'Input';
        $this->$inputFilter->setValue($value);

        if (!$this->$inputFilter->isValid($context)) {
            return $this->handleErrors($value, $this->$inputFilter->getMessages());
        }

        return $this->$inputFilter->getValue();
    }

    private function createTaskCmd(CaseEntity $case): CreateTaskCmd
    {
        $data = [
            'category' => CategoryEntity::CATEGORY_COMPLIANCE,
            'subCategory' => CategoryEntity::TASK_SUB_CATEGORY_NR,
            'description' => 'ERRU case has been automatically created',
            'actionDate' => date('Y-m-d'),
            'urgent' => 'Y',
            'case' => $case->getId(),
            'licence' => $case->getLicence()->getId(),
        ];

        return CreateTaskCmd::create($data);
    }

    /**
     * Command to update the document record with case and licence ids
     */
    private function createUpdateDocLinksCmd(DocumentEntity $document, CaseEntity $case, LicenceEntity $licence): UpdateDocLinksCmd
    {
        $data = [
            'id' => $document->getId(),
            'case' => $case->getId(),
            'licence' => $licence->getId(),
        ];

        return UpdateDocLinksCmd::create($data);
    }

    /**
     * Returns a queue command to send the error email
     */
    private function createErrorEmailCmd(int $id): CreateQueueCmd
    {
        return $this->emailQueue(SendErrorEmailCmd::class, ['id' => $id], $id);
    }

    /**
     * Returns the current list of errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->xmlStructureInput = $container->get('ComplianceXmlStructure');
        $this->complianceEpisodeInput = $container->get('ComplianceEpisodeInput');
        $this->seriousInfringementInput = $container->get('SeriousInfringementInput');
        $this->xmlMapping = $container->get('ComplianceEpisodeXmlMapping');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
