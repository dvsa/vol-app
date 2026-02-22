<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Report;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Service\Document\NamingServiceAwareInterface;
use Dvsa\Olcs\Api\Service\File\MimeNotAllowedException;
use Dvsa\Olcs\DocumentShare\Data\Object\File as DsFile;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Report\Upload as UploadCmd;
use Dvsa\Olcs\Utils\Helper\FileHelper;

/**
 * Upload
 */
final class Upload extends AbstractCommandHandler implements
    AuthAwareInterface,
    NamingServiceAwareInterface,
    TransactionedInterface,
    UploaderAwareInterface
{
    use AuthAwareTrait;
    use QueueAwareTrait;
    use UploaderAwareTrait;

    public const string ERR_MIME = 'ERR_MIME';

    protected $repoServiceName = 'Document';

    /**
     * Execute command
     *
     * @param UploadCmd $command Command
     *
     * @return Result
     * @throws ValidationException
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        // upload the file
        $identifier = $this->determineIdentifier($command);
        $file = $this->uploadFile($command, $identifier);

        // process the uploaded report
        $this->process($command, $file);

        return $this->result;
    }

    /**
     * Define file name(path)
     *
     * @param UploadCmd $command Upload command
     *
     * @return string
     */
    protected function determineIdentifier(UploadCmd $command)
    {
        $description = 'uploaded_report';
        $subCategory = null;

        switch ($command->getReportType()) {
            case RefData::REPORT_TYPE_COMM_LIC_BULK_REPRINT:
                $description = 'community_licence_bulk_reprint';
                $subCategory = SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE;
                break;
            case RefData::REPORT_TYPE_BULK_EMAIL:
                $description = 'bulk_upload_email_send';
                $subCategory = SubCategory::DOC_SUB_CATEGORY_REPORT_LETTER;
                break;
            case RefData::REPORT_TYPE_BULK_LETTER:
                $description = 'bulk_upload_letter_send';
                $subCategory = SubCategory::DOC_SUB_CATEGORY_REPORT_LETTER;
                break;
            case RefData::REPORT_TYPE_POST_SCORING_EMAIL:
                $description = 'post_scoring_email_send';
                $subCategory = SubCategory::DOC_SUB_CATEGORY_POST_SCORING_EMAIL;
                break;
        }

        $categoryReference = $this->getRepo()->getCategoryReference(Category::CATEGORY_REPORT);
        $subCategoryReference
            = ($subCategory !== null) ? $this->getRepo()->getSubCategoryReference($subCategory) : null;

        $extension = FileHelper::getExtension($command->getFilename());

        return $this->getNamingService()
            ->generateName($description, $extension, $categoryReference, $subCategoryReference);
    }

    /**
     * Upload file to Document storage
     *
     * @param UploadCmd $command Upload Command
     * @param string $identifier File name (path)
     *
     * @return DsFile
     * @throws ValidationException
     * @throws \Exception
     */
    protected function uploadFile(UploadCmd $command, $identifier)
    {
        $content = $command->getContent();

        $dsFile = new DsFile();

        if (!empty($content['tmp_name'])) {
            $dsFile->setContentFromStream($content['tmp_name']);

            if ('application/octet-stream' === $dsFile->getMimeType()) {
                $dsFile->setMimeType($content['type']);
            }
        } else {
            $dsFile->setContent(base64_decode($content));
        }

        try {
            $file = $this->getUploader()->upload($identifier, $dsFile);

            $this->result->addMessage('File uploaded');
            $this->result->addId('identifier', $file->getIdentifier());

            return $file;
        } catch (MimeNotAllowedException) {
            throw new ValidationException([self::ERR_MIME => self::ERR_MIME]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            unset($dsFile);
        }
    }

    /**
     * Process the uploaded report
     *
     * @param string $reportType Report type
     * @param DsFile $file The uploaded file
     *
     * @return void
     */
    protected function process($command, DsFile $file)
    {
        switch ($command->getReportType()) {
            case RefData::REPORT_TYPE_COMM_LIC_BULK_REPRINT:
                $this->result->merge(
                    $this->handleSideEffect(
                        $this->createQueue(
                            null,
                            Queue::TYPE_COMM_LIC_BULK_REPRINT,
                            [
                                'identifier' => $file->getIdentifier(),
                                'user' => $this->getCurrentUser()->getId(),
                            ]
                        )
                    )
                );
                break;
            case RefData::REPORT_TYPE_BULK_EMAIL:
                $this->result->merge(
                    $this->handleSideEffect(
                        $this->createQueue(
                            null,
                            Queue::TYPE_EMAIL_BULK_UPLOAD,
                            [
                                'identifier' => $file->getIdentifier(),
                                'user' => $this->getCurrentUser()->getId(),
                                'templateName' => $command->getName(),
                            ]
                        )
                    )
                );
                break;
            case RefData::REPORT_TYPE_BULK_LETTER:
                $this->result->merge(
                    $this->handleSideEffect(
                        $this->createQueue(
                            null,
                            Queue::TYPE_LETTER_BULK_UPLOAD,
                            [
                                'identifier' => $file->getIdentifier(),
                                'user' => $this->getCurrentUser()->getId(),
                                'templateSlug' => $command->getTemplateSlug(),
                            ]
                        )
                    )
                );
                break;
            case RefData::REPORT_TYPE_POST_SCORING_EMAIL:
                $this->result->merge(
                    $this->handleSideEffect(
                        $this->createQueue(
                            null,
                            Queue::TYPE_POST_SCORING_EMAIL,
                            [
                                'identifier' => $file->getIdentifier(),
                            ]
                        )
                    )
                );
                break;
        }
    }
}
