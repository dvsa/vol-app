<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
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
        $case = $this->getRepo()->fetchById($command->getCase());

        //generate the xml to send to national register
        $xml = $this->msiResponseService->create($case);

        $erruRequest = $case->getErruRequest();

        //save the xml into the document store
        $xmlDocumentCmd = $this->createDocumentCommand($xml, $erruRequest->getNotificationNumber(), $case);
        $result = $this->handleSideEffect($xmlDocumentCmd);

        //get the document record so we can link it to the erru request
        $docRepo = $this->getRepo('Document');
        $responseDocument = $docRepo->fetchById($result->getId('document'));

        $erruRequest->queueErruResponse(
            $this->getCurrentUser(),
            new \DateTime($this->msiResponseService->getResponseDateTime()),
            $responseDocument
        );

        $requestId = $erruRequest->getId();
        $this->getRepo('ErruRequest')->save($erruRequest);

        $result->addMessage(self::MSG_RESPONSE_CREATED);
        $result->addId('case', $case->getId());
        $result->addId('erruRequest', $requestId);

        $sendResponseCmd = SendResponseCmd::create(['id' => $requestId]);
        $result->merge($this->handleSideEffect($sendResponseCmd));

        return $result;
    }

    /**
     * Returns an upload command to add the response XML to the doc store
     *
     * @param string      $content            this will be xml
     * @param string      $notificationNumber this will be a GUID
     * @param CasesEntity $case               case entity
     *
     * @return UploadCmd
     */
    private function createDocumentCommand($content, $notificationNumber, CasesEntity $case)
    {
        $data = [
            'content' => base64_encode($content),
            'category' => CategoryEntity::CATEGORY_COMPLIANCE,
            'subCategory' => CategoryEntity::DOC_SUB_CATEGORY_NR,
            'filename' => 'msiresponse.xml',
            'description' => sprintf(CreateResponse::RESPONSE_DOCUMENT_DESCRIPTION, $notificationNumber),
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
