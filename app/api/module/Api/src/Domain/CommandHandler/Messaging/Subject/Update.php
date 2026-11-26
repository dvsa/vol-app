<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Subject;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\MessagingSubject as Repo;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Messaging\Subject\Update as Cmd;

final class Update extends AbstractCommandHandler
{
    protected $extraRepos = [Repo::class];

    /**
     * @param CommandInterface|Cmd $command
     */
    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        $repo = $this->getRepo(Repo::class);

        $subject = $repo->fetchById($command->getId(), Query::HYDRATE_OBJECT, $command->getVersion());
        $subject->setDescription($command->getDescription());
        $subject->setCategory($repo->getReference(Category::class, $command->getCategory()));
        $subject->setSubCategory($repo->getReference(SubCategory::class, $command->getSubCategory()));
        $subject->setIsActive($command->getIsActive());

        $repo->save($subject);

        $this->result->addId('messagingSubject', $subject->getId());
        $this->result->addMessage('Messaging subject updated');

        return $this->result;
    }
}
