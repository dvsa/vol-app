<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\BulkSend;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore as GenerateAndStoreCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\BulkSend\Letter as LetterCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\Api\Entity\Doc\DocTemplate;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\DocumentShare\Data\Object\File as ContentStoreFile;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Bulk send letters
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class Letter extends AbstractCommandHandler implements
    UploaderAwareInterface,
    AuthAwareInterface
{
    use AuthAwareTrait;
    use UploaderAwareTrait;

    public const int EXPECTED_ITEMS_IN_ROW = 1;

    protected $repoServiceName = 'DocTemplate';

    /** @var array */
    private $licenceIds = [];

    private $docTemplateIdentifier;

    private $userId;

    /**
     * Handle command
     *
     * @param LetterCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var ContentStoreFile $file */
        $file = $this->uploader->download(
            $command->getDocumentIdentifier()
        );

        $fp = fopen('php://memory', 'r+');
        fputs($fp, $file->getContent());

        $this->userId = $command->getUser();

        $this->processFile($fp, $command);

        return $this->result;
    }

    /**
     * Prepare letters for each licence specified in the CSV
     *
     * @param resource $fp
     * @param $command
     *
     */
    private function processFile($fp, $command)
    {
        $template = $this->getRepo()->fetchByTemplateSlug($command->getTemplateSlug());
        $this->docTemplateIdentifier = $template->getDocument()->getIdentifier();

        rewind($fp);
        while (($row = fgetcsv($fp)) !== false) {
            $licenceId = $row[0];
            if ($licenceId != 'licence_id' && !in_array($licenceId, $this->licenceIds)) {
                $this->licenceIds[] = $licenceId;
                $this->generateDocument($template, $licenceId);
            }
        }

        $this->result->addMessage('Processing completed successfully');
    }

    /**
     * Generate document
     *
     * @param DocTemplate $template template
     * @param $licenceId
     *
     * @return void
     * @throws ValidationException
     */
    protected function generateDocument(DocTemplate $template, $licenceId)
    {
        $dtoData = [
            'template' => $this->docTemplateIdentifier,
            'query' => ['licence' => $licenceId, 'user' => $this->userId],
            'description' => $template->getDescription(),
            'category' => Category::CATEGORY_REPORT,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_REPORT_LETTER,
            'isExternal' => false,
            'isScan' => false,
            'disableBookmarks' => false,
            'licence' => $licenceId,
            'dispatch' => true
        ];

        $this->result->merge($this->handleSideEffect(GenerateAndStoreCmd::create($dtoData)));
    }
}
