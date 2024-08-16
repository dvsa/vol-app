<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Correspondence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Access Correspondence
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class AccessCorrespondence extends AbstractCommandHandler
{
    protected $repoServiceName = 'Correspondence';

    /**
     * @param \Dvsa\Olcs\Transfer\Command\Correspondence\AccessCorrespondence $command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $repo = $this->getRepo();

        /** @var \Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox $correspondence */
        $correspondence = $repo->fetchById(
            $command->getId(),
            \Doctrine\ORM\Query::HYDRATE_OBJECT
        );

        $correspondence->setAccessed('Y');

        $repo->save($correspondence);

        $result = new Result();
        $result->addId('correspondence', $correspondence->getId());
        $result->addMessage('Correspondence updated successfully');

        return $result;
    }
}
