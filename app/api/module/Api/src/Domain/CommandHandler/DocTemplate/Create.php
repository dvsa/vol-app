<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\DocTemplate;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Query\Document\ByDocumentStoreId;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\Api\Entity\Doc\DocTemplate;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Service\Document\NamingServiceAwareInterface;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;

/**
 * Create
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class Create extends AbstractCommandHandler implements
    TransactionedInterface,
    UploaderAwareInterface,
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
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        //Generate path identifier for docstore
        $identifier = DocTemplate::TEMPLATE_PATH_PREFIXES[$command->getTemplateFolder()] . $command->getFilename();

        //Dont overwrite exisitng documents on Create
        if ($this->identifierExists($identifier)) {
            throw new RuntimeException('Template document with this identifier already exists!');
        }

        //Push to document store and then create Document entity and save it.
        $file = $this->uploadFile($command, $identifier);
        $this->result->merge($this->createDocument($command, $file, $identifier));

        $newDocTemplate = $this->createDocTemplateEntity($command);
        $this->getRepo()->save($newDocTemplate);

        $this->result->addId('docTemplate', $newDocTemplate->getId());
        $this->result->addMessage('DocTemplate Created Successfully');

        return $this->result;
    }

    /**
     * @return bool
     */
    protected function identifierExists(string $identifier)
    {
        $existingDocResult = $this->handleQuery(
            ByDocumentStoreId::create(['documentStoreId' => $identifier])
        );
        return count($existingDocResult) === 0 ? false : true;
    }

    /**
     * @param TransferCmd\DocTemplate\Create $command
     * @return DocTemplate
     */
    protected function createDocTemplateEntity(TransferCmd\DocTemplate\Create $command)
    {
        $letterType = null;
        if ($command->getLetterType()) {
            $letterType = $this->getRepo('LetterType')->getReference(
                \Dvsa\Olcs\Api\Entity\Letter\LetterType::class,
                $command->getLetterType()
            );
        }

        return DocTemplate::createNew(
            $this->getRepo('Category')->getReference(Category::class, $command->getCategory()),
            $this->getRepo('SubCategory')->getReference(SubCategory::class, $command->getSubCategory()),
            $command->getDescription(),
            $this->getRepo('Document')->getReference(Document::class, $this->result->getIds()['document']),
            $command->getIsNi(),
            $command->getSuppressFromOp(),
            $command->getTemplateSlug(),
            $letterType,
            $this->getRepo('User')->getReference(User::class, $this->getCurrentUser())
        );
    }
}
