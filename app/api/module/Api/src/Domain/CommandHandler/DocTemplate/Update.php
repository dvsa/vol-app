<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\DocTemplate;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\Api\Entity\Doc\DocTemplate;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Service\Document\NamingServiceAwareInterface;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class Update extends AbstractCommandHandler implements
    TransactionedInterface,
    UploaderAwareInterface,
    NamingServiceAwareInterface,
    AuthAwareInterface
{
    use UploaderAwareTrait;
    use AuthAwareTrait;
    use DocTemplateTrait;

    public const ERR_MIME = 'ERR_MIME';
    public const ERR_EBSR_MIME = 'ERR_EBSR_MIME';

    protected $repoServiceName = 'DocTemplate';

    protected $extraRepos = ['Category', 'SubCategory', 'Document', 'User', 'LetterType'];

    /**
     * Execute command
     *
     * @param CommandInterface $command Command
     *
     * @return Result
     * @throws ValidationException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var DocTemplate $docTemplate
         */
        $docTemplate = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);

        //Update DocTemplate properties
        $this->updateDocTemplate($docTemplate, $command);

        //a new file was provided, so also update the Document record for this DocTemplate
        if (!empty($command->getContent())) {
            $this->deleteAndUpload($command, $docTemplate->getDocument());
            $this->updateDocTemplateWithFile($docTemplate);
        }

        $this->getRepo()->save($docTemplate);

        $this->result->addId('docTemplate', $docTemplate->getId());
        $this->result->addMessage('DocTemplate Updated Successfully');

        return $this->result;
    }

    public function updateDocTemplate(DocTemplate $docTemplate, CommandInterface $command)
    {
        $letterType = empty($command->getLetterType()) ? null :
            $this->getRepo('LetterType')->getReference(
                \Dvsa\Olcs\Api\Entity\Letter\LetterType::class,
                $command->getLetterType()
            );

        $docTemplate->updateMeta(
            $this->getRepo('Category')->getReference(Category::class, $command->getCategory()),
            $this->getRepo('SubCategory')->getReference(SubCategory::class, $command->getSubCategory()),
            $command->getDescription(),
            $command->getIsNi(),
            $command->getSuppressFromOp(),
            $letterType
        );

        $docTemplate->setLetterType($letterType);
    }

    public function updateDocTemplateWithFile(DocTemplate $docTemplate)
    {
        $docTemplate->updateDocument(
            $this->getRepo('Document')->getReference(Document::class, $this->result->getIds()['document'])
        );
    }

    /**
     * Deletes existing document being replaced by this edit
     *
     * @return void
     * @throws ValidationException
     */
    protected function deleteAndUpload(CommandInterface $command, Document $oldDocument)
    {
        // Delete exisitng attached document
        $cmd = TransferCmd\Document\DeleteDocument::create(['id' => $oldDocument->getId()]);
        $this->result->merge($this->handleSideEffect($cmd));

        //Generate identifier and upload file to store, create document record in DB
        $identifier = DocTemplate::TEMPLATE_PATH_PREFIXES[$command->getTemplateFolder()] . $command->getFilename();
        $file = $this->uploadFile($command, $identifier);
        $this->result->merge($this->createDocument($command, $file, $identifier));
    }
}
