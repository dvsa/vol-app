<?php

/**
 * Schedule41Reset.php
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Schedule41\ResetS4;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Application\S4;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Schedule41Reset Command Handler
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class Schedule41Reset extends AbstractCommandHandler
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $application ApplicationEntity */
        $application = $this->getRepo()->fetchById($command->getId());

        $criteria = Criteria::create();
        $criteria->where(
            $criteria->expr()->eq('outcome', $this->getRepo()->getRefdataReference(S4::STATUS_APPROVED))
        );

        $s4s = $application->getS4s()->matching($criteria);

        /** @var \Dvsa\Olcs\Api\Entity\Application\S4 $s4 */
        foreach ($s4s as $s4) {
            $this->result->merge(
                $this->handleSideEffect(
                    ResetS4::create(
                        [
                            'id' => $s4->getId(),
                        ]
                    )
                )
            );
        }

        // remove any TEX task associated to the application
        $this->result->merge(
            $this->handleSideEffect(
                \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::create(
                    ['id' => $application->getId()]
                )
            )
        );

        $this->deleteUnpublishedLinks($application);

        $this->result->addMessage('Schedule 4/1 reset.');

        return $this->result;
    }

    /**
     * Delete any unpublished links that are in Section 3
     */
    private function deleteUnpublishedLinks(ApplicationEntity $application)
    {
        foreach ($application->getPublicationLinks() as $publicationLink) {
            /* @var $publicationLink \Dvsa\Olcs\Api\Entity\Publication\PublicationLink */
            if (
                $publicationLink->getPublication()->isNew() &&
                $publicationLink->getPublicationSection()->isSection3()
            ) {
                $this->result->merge(
                    $this->handleSideEffect(
                        \Dvsa\Olcs\Transfer\Command\Publication\DeletePublicationLink::create(
                            ['id' => $publicationLink->getId()]
                        )
                    )
                );
            }
        }
    }
}
