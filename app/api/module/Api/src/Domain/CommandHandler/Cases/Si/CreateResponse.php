<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Cases\Si\SendResponse as SendResponseCmd;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Service\Nr\MsiResponse as MsiResponseService;
use Dvsa\Olcs\Transfer\Command\Cases\Si\CreateResponse as CreateErruResponseCmd;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Psr\Container\ContainerInterface;

final class CreateResponse extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface
{
    use AuthAwareTrait;

    public const RESPONSE_DOCUMENT_DESCRIPTION = 'ERRU MSI response for business case ID: %s';
    public const MSG_RESPONSE_CREATED = 'Msi Response created';
    public const MSG_RESPONSE_NOT_REQUIRED = 'No requested penalties - Msi Response not required';

    protected $repoServiceName = 'Cases';

    protected $extraRepos = [
        'ErruRequest',
        'Document'
    ];

    /**
     * @var MsiResponseService
     */
    protected $msiResponseService;

    /**
     * Create the erru response, then trigger side effect to send it
     *
     * @param CommandInterface $command the command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var CasesEntity $case
         * @var CreateErruResponseCmd $command
         */
        $caseId = $command->getCase();
        $case = $this->getRepo()->fetchById($caseId);
        $erruRequest = $case->getErruRequest();
        $requestId = $erruRequest->getId();

        $this->result->addId('case', $caseId);
        $this->result->addId('erruRequest', $requestId);

        //if there are no requested penalties, then we mark the case as sent, although a response is not required
        if (!$case->hasErruRequestedPenalties()) {
            $erruRequest->setMsiType($this->refData(ErruRequest::SENT_CASE_TYPE));
            $this->getRepo('ErruRequest')->save($erruRequest);
            $this->result->addMessage(self::MSG_RESPONSE_NOT_REQUIRED);

            return $this->result;
        }

        //generate the xml to send to national register
        $xml = $this->msiResponseService->create($case);

        //save the xml into the document store
        $xmlDocumentCmd = $this->createDocumentCommand($xml, $erruRequest->getNotificationNumber(), $case);
        $this->result->merge(
            $this->handleSideEffect($xmlDocumentCmd)
        );

        //get the document record so we can link it to the erru request
        $docRepo = $this->getRepo('Document');
        $responseDocument = $docRepo->fetchById($this->result->getId('document'));

        $erruRequest->queueErruResponse(
            $this->getCurrentUser(),
            new \DateTime($this->msiResponseService->getResponseDateTime()),
            $responseDocument
        );

        $this->getRepo('ErruRequest')->save($erruRequest);

        $this->result->addMessage(self::MSG_RESPONSE_CREATED);

        $sendResponseCmd = SendResponseCmd::create(['id' => $requestId]);
        $this->result->merge($this->handleSideEffect($sendResponseCmd));

        return $this->result;
    }

    /**
     * Returns an upload command to add the response XML to the doc store
     *
     * @param string      $content            this will be xml
     * @param string      $notificationNumber this will be a GUID
     * @param CasesEntity $case               case entity
     */
    private function createDocumentCommand(string $content, string $notificationNumber, CasesEntity $case): UploadCmd
    {
        $data = [
            'content' => base64_encode($content),
            'category' => CategoryEntity::CATEGORY_COMPLIANCE,
            'subCategory' => CategoryEntity::DOC_SUB_CATEGORY_NR,
            'filename' => 'msiresponse.xml',
            'description' => sprintf(self::RESPONSE_DOCUMENT_DESCRIPTION, $notificationNumber),
            'case' => $case->getId(),
            'licence' => $case->getLicence()->getId()
        ];

        return UploadCmd::create($data);
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->msiResponseService = $container->get(MsiResponseService::class);
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
