<?php

/**
 * Schedule41Approve.php
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Schedule41\ApproveS4;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Schedule41Approve Command Handler
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class Schedule41Approve extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\Application\Schedule41Approve $command command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $application ApplicationEntity */
        $application = $this->getRepo()->fetchById($command->getId());

        $isTrueS4 = $command->getTrueS4() === 'Y';

        $criteria = Criteria::create();
        $criteria->where(
            $criteria->expr()->eq('agreedDate', null)
        );
        $criteria->andWhere(
            $criteria->expr()->eq('outcome', null)
        );

        $s4s = $application->getS4s()->matching($criteria);

        $result = new Result();

        /* @var $s4 \Dvsa\Olcs\Api\Entity\Application\S4 */
        foreach ($s4s as $s4) {
            $result->merge(
                $this->handleSideEffect(
                    ApproveS4::create(
                        [
                            'id' => $s4->getId(),
                            'isTrueS4' => $isTrueS4 ? 'Y' : 'N',
                            'outcome' => null
                        ]
                    )
                )
            );
        }

        if (!$s4s->isEmpty()) {
            // publish application
            $publicationSection = $this->getPublicationSection($application, $command->getTrueS4() === 'Y');

            $result->merge($this->createPublication($application, $publicationSection));
            if (!$isTrueS4) {
                $result->merge($this->createTexTask($application));
            }

            $result->addMessage('Schedule 4/1 approved.');
        }

        return $result;
    }

    /**
     * Create a publicaction
     *
     * @param ApplicationEntity $application        application
     * @param int               $publicationSection Publication Section ID
     *
     * @return Result
     */
    protected function createPublication(ApplicationEntity $application, $publicationSection)
    {
        return $this->handleSideEffect(
            \Dvsa\Olcs\Transfer\Command\Publication\Application::create(
                [
                    'id' => $application->getId(),
                    'trafficArea' => $application->getTrafficArea()->getId(),
                    'publicationSection' => $publicationSection
                ]
            )
        );
    }

    /**
     * Get the publication section for the application
     *
     * @param ApplicationEntity $application application
     * @param bool              $isTrue      is true
     *
     * @return int
     */
    protected function getPublicationSection(ApplicationEntity $application, $isTrue)
    {
        if ($application->getTrafficArea()->getIsNi()) {
            return $this->getPublicationSectionNi($application, $isTrue);
        } else {
            return $this->getPublicationSectionGb($application, $isTrue);
        }
    }

    /**
     * Get publication section ID for a GB application
     *
     * @param ApplicationEntity $application application
     * @param bool              $isTrue      is true
     *
     * @return int
     */
    protected function getPublicationSectionGb(ApplicationEntity $application, $isTrue)
    {
        // for new Apps
        if ($application->isNew()) {
            return \Dvsa\Olcs\Api\Entity\Publication\PublicationSection::SCHEDULE_4_NEW;
        }

        // for variations depends on if its a true S4
        if ($isTrue) {
            return \Dvsa\Olcs\Api\Entity\Publication\PublicationSection::SCHEDULE_4_TRUE;
        } else {
            return \Dvsa\Olcs\Api\Entity\Publication\PublicationSection::SCHEDULE_4_UNTRUE;
        }
    }

    /**
     * Get publication section ID for an NI application
     *
     * @param ApplicationEntity $application application
     * @param bool              $isTrue      is true
     *
     * @return int
     */
    protected function getPublicationSectionNi(ApplicationEntity $application, $isTrue)
    {
        // for new Apps
        if ($application->isNew()) {
            return \Dvsa\Olcs\Api\Entity\Publication\PublicationSection::SCHEDULE_1_NI_NEW;
        }

        // for variations depends on if its a true S4
        if ($isTrue) {
            return \Dvsa\Olcs\Api\Entity\Publication\PublicationSection::SCHEDULE_1_NI_TRUE;
        } else {
            return \Dvsa\Olcs\Api\Entity\Publication\PublicationSection::SCHEDULE_1_NI_UNTRUE;
        }
    }

    /**
     * Create a TEX task
     *
     * @param ApplicationEntity $application application
     *
     * @return Result
     */
    protected function createTexTask(ApplicationEntity $application)
    {
        return $this->handleSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CreateTexTask::create(
                [
                    'id' => $application->getId(),
                ]
            )
        );
    }
}
