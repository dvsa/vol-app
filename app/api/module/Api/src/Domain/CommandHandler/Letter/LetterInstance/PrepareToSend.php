<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Document\CreateDocument as CreateDocumentCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\DocumentGeneratorAwareInterface;
use Dvsa\Olcs\Api\Domain\DocumentGeneratorAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Api\Entity\Letter\MasterTemplate;
use Dvsa\Olcs\Api\Service\ConvertToPdf\ConvertHtmlToPdfInterface;
use Dvsa\Olcs\Api\Service\Document\NamingServiceAwareInterface;
use Dvsa\Olcs\Api\Service\Letter\LetterPreviewService;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstance\PrepareToSend as Cmd;
use Psr\Container\ContainerInterface;

/**
 * PrepareToSend LetterInstance
 *
 * Converts letter HTML to PDF, uploads to docstore, creates a Document entity
 * so the existing printAction/PrintLetterCmd flow can handle sending.
 */
final class PrepareToSend extends AbstractCommandHandler implements
    TransactionedInterface,
    DocumentGeneratorAwareInterface,
    AuthAwareInterface,
    NamingServiceAwareInterface
{
    use DocumentGeneratorAwareTrait;
    use AuthAwareTrait;

    protected $repoServiceName = 'LetterInstance';

    protected $extraRepos = ['Document', 'MasterTemplate'];

    private LetterPreviewService $previewService;

    private ConvertHtmlToPdfInterface $convertHtmlToPdf;

    private $contentStore;

    public function handleCommand(CommandInterface $command): \Dvsa\Olcs\Api\Domain\Command\Result
    {
        /** @var Cmd $command */

        /** @var LetterInstanceEntity $letterInstance */
        $letterInstance = $this->getRepo()->fetchUsingId($command);

        // Validate state
        if (!$letterInstance->isDraft() && !$letterInstance->isReady()) {
            throw new ValidationException(['Letter instance must be in DRAFT or READY status']);
        }

        // Get master template for rendering
        $masterTemplate = $this->getMasterTemplate($letterInstance);

        // Render preview HTML
        $previewHtml = $this->previewService->renderPreview($letterInstance, $masterTemplate);

        // Convert HTML to PDF via Gotenberg
        $tempPdfFile = tempnam(sys_get_temp_dir(), 'letter_') . '.pdf';
        $tempFiles = [$tempPdfFile];
        try {
            $this->convertHtmlToPdf->convertHtml($previewHtml, $tempPdfFile);

            // Collect PDF-type appendices for merging
            $appendixPdfFiles = [];
            foreach ($letterInstance->getLetterInstanceAppendices() as $appendix) {
                if ($appendix->isPdf()) {
                    $version = $appendix->getLetterAppendixVersion();
                    if ($version && $version->hasDocument()) {
                        $identifier = $version->getDocumentIdentifier();
                        if ($identifier) {
                            try {
                                $file = $this->contentStore->read($identifier);
                                if ($file !== null) {
                                    $appendixTempFile = tempnam(sys_get_temp_dir(), 'appendix_') . '.pdf';
                                    file_put_contents($appendixTempFile, $file->getContent());
                                    $appendixPdfFiles[] = $appendixTempFile;
                                    $tempFiles[] = $appendixTempFile;
                                }
                            } catch (\Exception $e) {
                                // Skip this appendix if content store fails
                            }
                        }
                    }
                }
            }

            // Merge PDFs if there are appendices
            if (!empty($appendixPdfFiles)) {
                $mergedPdfFile = tempnam(sys_get_temp_dir(), 'merged_') . '.pdf';
                $tempFiles[] = $mergedPdfFile;
                $this->convertHtmlToPdf->mergePdfs(
                    array_merge([$tempPdfFile], $appendixPdfFiles),
                    $mergedPdfFile
                );
                $pdfContent = file_get_contents($mergedPdfFile);
            } else {
                $pdfContent = file_get_contents($tempPdfFile);
            }
        } finally {
            foreach ($tempFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }

        // Generate filename using NamingService
        $letterType = $letterInstance->getLetterType();
        $description = $letterType ? $letterType->getName() : 'Letter';
        $category = $letterType ? $letterType->getCategory() : null;
        $subCategory = $letterType ? $letterType->getSubCategory() : null;

        // Build entity data for NamingService context
        $entityData = $this->buildEntityData($letterInstance);
        $entity = !empty($entityData)
            ? $this->determineEntityFromCommand($entityData)
            : null;

        $fileName = $this->getNamingService()->generateName(
            $description,
            'pdf',
            $category,
            $subCategory,
            $entity
        );

        // Upload PDF to content store
        $file = $this->getDocumentGenerator()->uploadGeneratedContent($pdfContent, $fileName);

        // Build Document metadata with docTemplate reference for canEmail() check
        $metadata = json_encode([
            'details' => [
                'documentTemplate' => $command->getDocTemplate(),
            ],
        ]);

        // Create Document entity via side effect
        $documentData = [
            'filename' => $fileName,
            'identifier' => $file->getIdentifier(),
            'size' => $file->getSize(),
            'description' => $description . ' - ' . ($letterInstance->getReference() ?? ''),
            'isExternal' => false,
            'category' => $category ? $category->getId() : null,
            'subCategory' => $subCategory ? $subCategory->getId() : null,
            'metadata' => $metadata,
        ];

        // Link entity associations
        if ($letterInstance->getLicence()) {
            $documentData['licence'] = $letterInstance->getLicence()->getId();
        }
        if ($letterInstance->getApplication()) {
            $documentData['application'] = $letterInstance->getApplication()->getId();
        }
        if ($letterInstance->getCase()) {
            $documentData['case'] = $letterInstance->getCase()->getId();
        }
        if ($letterInstance->getBusReg()) {
            $documentData['busReg'] = $letterInstance->getBusReg()->getId();
        }
        if ($letterInstance->getTransportManager()) {
            $documentData['transportManager'] = $letterInstance->getTransportManager()->getId();
        }
        if ($letterInstance->getIrhpApplication()) {
            $documentData['irhpApplication'] = $letterInstance->getIrhpApplication()->getId();
        }

        if ($this->getCurrentUser()) {
            $documentData['user'] = $this->getCurrentUser()->getId();
        }

        $createDocResult = $this->handleSideEffect(CreateDocumentCmd::create($documentData));
        $this->result->merge($createDocResult);

        $documentId = $createDocResult->getId('document');

        // Link Document to LetterInstance
        if ($documentId) {
            $document = $this->getRepo('Document')->fetchById($documentId);
            $letterInstance->setDocument($document);
        }

        // Update LetterInstance status to READY
        if ($letterInstance->isDraft()) {
            $readyStatus = $this->getRepo()->getRefdataReference(LetterInstanceEntity::STATUS_READY);
            $letterInstance->setStatus($readyStatus);
        }

        $this->getRepo()->save($letterInstance);

        $this->result->addId('document', $documentId);
        $this->result->addMessage('Letter prepared for sending');

        return $this->result;
    }

    /**
     * Get the master template to use for rendering
     */
    private function getMasterTemplate(LetterInstanceEntity $letterInstance): ?MasterTemplate
    {
        $letterType = $letterInstance->getLetterType();
        if ($letterType !== null && $letterType->getMasterTemplate() !== null) {
            return $letterType->getMasterTemplate();
        }

        try {
            $repo = $this->getRepo('MasterTemplate');
            $result = $repo->fetchList(
                \Dvsa\Olcs\Transfer\Query\Letter\MasterTemplate\GetList::create([
                    'isDefault' => true,
                    'locale' => MasterTemplate::LOCALE_EN_GB,
                    'limit' => 1,
                ])
            );

            if (count($result) > 0) {
                return $result[0];
            }
        } catch (\Exception $e) {
            // Continue without template
        }

        return null;
    }

    /**
     * Build entity data array from LetterInstance for NamingService/determineEntityFromCommand
     */
    private function buildEntityData(LetterInstanceEntity $letterInstance): array
    {
        $data = [];

        if ($letterInstance->getCase()) {
            $data['case'] = $letterInstance->getCase()->getId();
        }
        if ($letterInstance->getApplication()) {
            $data['application'] = $letterInstance->getApplication()->getId();
        }
        if ($letterInstance->getTransportManager()) {
            $data['transportManager'] = $letterInstance->getTransportManager()->getId();
        }
        if ($letterInstance->getBusReg()) {
            $data['busReg'] = $letterInstance->getBusReg()->getId();
        }
        if ($letterInstance->getLicence()) {
            $data['licence'] = $letterInstance->getLicence()->getId();
        }
        if ($letterInstance->getIrhpApplication()) {
            $data['irhpApplication'] = $letterInstance->getIrhpApplication()->getId();
        }

        return $data;
    }

    /**
     * Factory method for dependency injection
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): self
    {
        $this->previewService = $container->get(LetterPreviewService::class);
        $this->convertHtmlToPdf = $container->get('ConvertToPdf');
        $this->contentStore = $container->get('ContentStore');
        return parent::__invoke($container, $requestedName, $options);
    }
}
