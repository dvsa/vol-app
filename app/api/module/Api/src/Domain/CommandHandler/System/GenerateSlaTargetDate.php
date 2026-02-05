<?php

/**
 * Generate SlaTargetDate
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\System;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculatorInterface;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Api\Entity\Submission\Submission as SubmissionEntity;
use Dvsa\Olcs\Api\Entity\System\SlaTargetDate as SlaTargetDateEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke as ProposeToRevokeEntity;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Entity\System\Sla as SlaEntity;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate as GenerateSlaTargetDateCmd;
use Psr\Container\ContainerInterface;

/**
 * Generate SlaTargetDate
 */
final class GenerateSlaTargetDate extends AbstractCommandHandler
{
    protected $repoServiceName = 'SlaTargetDate';

    protected $extraRepos = ['Pi', 'Submission', 'Sla', 'ProposeToRevoke','Statement'];

    /**
     * @var SlaCalculatorInterface
     */
    private $slaService;

    /**
     * @param CommandInterface $command Command
     * @return Result
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();
        /** @var GenerateSlaTargetDateCmd  $command */
        if ($command->getPi() !== null) {
            $result->merge(
                $this->generateForEntity('Pi', $command->getPi(), ['pi', 'pi_hearing'])
            );
        } elseif ($command->getSubmission() !== null) {
            $result->merge(
                $this->generateForEntity('Submission', $command->getSubmission(), ['submission'])
            );
        } elseif ($command->getProposeToRevoke() !== null) {
            $result->merge(
                $this->generateForEntity('ProposeToRevoke', $command->getProposeToRevoke(), ['ptr'])
            );
        } elseif ($command->getStatement() !== null) {
            $result->merge(
                $this->generateForEntity('Statement', $command->getStatement(), ['statement'])
            );
        }

        return $result;
    }

    /**
     * Generates and saves SLA for given entity
     *
     * @param string $repoName   Repo Name
     * @param int    $id         Id
     * @param array  $categories Categories
     *
     * @return Result
     * @throws RuntimeException
     */
    private function generateForEntity($repoName, $id, array $categories)
    {
        $result = new Result();

        $entity = $this->getRepo($repoName)->fetchById($id);

        // we need a traffic area so we can calculate SLAs
        if ($entity->getCase()->isTm()) {
            // no licence for TM cases so using English TA
            $trafficArea = $this->getRepo()->getReference(
                TrafficAreaEntity::class,
                TrafficAreaEntity::SE_MET_TRAFFIC_AREA_CODE
            );
        } else {
            $trafficArea = $entity->getCase()->getLicence()->getTrafficArea();
        }

        $this->processSlaTargetDates($entity, $trafficArea, $categories);

        $this->getRepo($repoName)->save($entity);

        $result->addMessage('SLA Target Dates successfully saved');

        return $result;
    }

    /**
     * Process SLA Target Dates for given entity (updates SlaTargetDates collection)
     *
     * @param PiEntity|SubmissionEntity|ProposeToRevokeEntity $entity      Entity
     * @param TrafficAreaEntity                               $trafficArea Traffic Area
     * @param array                                           $categories  Categories
     *
     * @return array
     * @throws RuntimeException
     */
    private function processSlaTargetDates($entity, TrafficAreaEntity $trafficArea, array $categories)
    {
        $reduced = [];

        $slas = $this->getRepo('Sla')->fetchByCategories($categories, Query::HYDRATE_OBJECT);

        foreach ($slas as $sla) {
            /** @var SlaEntity $sla*/
            $getMethod = 'get' . ucfirst($sla->getCompareTo());

            if ($entity->$getMethod() !== null) {
                // get agreed date
                $agreedDate = ($entity->$getMethod() instanceof \DateTime)
                    ? $entity->$getMethod() : new \DateTime($entity->$getMethod());

                if ($sla->appliesTo($agreedDate)) {
                    // calculate target date
                    $targetDate = $this->slaService->applySla($agreedDate, $sla, $trafficArea);

                    $slaTargetDate = $entity->getSlaTargetDates()->get($sla->getId());

                    if ($slaTargetDate instanceof SlaTargetDateEntity) {
                        // update existing record
                        $slaTargetDate->setAgreedDate($agreedDate);
                    } else {
                        // create new record
                        $slaTargetDate = new SlaTargetDateEntity(
                            $entity,
                            $agreedDate
                        );
                        $slaTargetDate->setSla($sla);
                    }

                    // set new target date
                    $slaTargetDate->setTargetDate($targetDate);

                    $entity->getSlaTargetDates()->set($sla->getId(), $slaTargetDate);

                    $reduced[] = $sla->getId();
                }
            }
        }

        // remove the rest
        foreach ($entity->getSlaTargetDates() as $key => $item) {
            if (!in_array($key, $reduced)) {
                $entity->getSlaTargetDates()->remove($key);
            }
        }

        return $reduced;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        parent::__invoke($fullContainer, $requestedName, $options);
        $this->slaService = $container->get(SlaCalculatorInterface::class);

        return $this;
    }
}
