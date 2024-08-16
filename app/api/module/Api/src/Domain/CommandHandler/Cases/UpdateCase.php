<?php

/**
 * Update a case
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases;

use Doctrine\ORM\Query;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Transfer\Command\Cases\UpdateCase as UpdateCaseCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Update a Case
 */
final class UpdateCase extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Cases';

    /**
     * Updates a case
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var UpdateCaseCmd $command **/
        /** @var CasesEntity $case **/
        $result = new Result();

        $case = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $caseType = $this->getRepo()->getRefdataReference($command->getCaseType());
        $categorys = $this->processCategorys($command->getCategorys());
        $outcomes = $this->processOutcomes($command->getOutcomes());

        $case->update(
            $caseType,
            $categorys,
            $outcomes,
            $command->getEcmsNo(),
            $command->getDescription()
        );

        $this->getRepo()->save($case);
        $result->addMessage('Case updated');
        $result->addId('case', $case->getId());

        return $result;
    }

    /**
     * Returns collection of categorys.
     *
     * @param array $categorys
     * @return ArrayCollection
     */
    private function processCategorys($categorys)
    {
        $result = new ArrayCollection();
        if (!empty($categorys)) {
            foreach ($categorys as $category) {
                $result->add($this->getRepo()->getRefdataReference($category));
            }
        }
        return $result;
    }

    /**
     * Returns collection of outcomes.
     *
     * @param array $outcomes
     * @return ArrayCollection
     */
    private function processOutcomes($outcomes)
    {
        $result = new ArrayCollection();
        if (!empty($outcomes)) {
            foreach ($outcomes as $outcome) {
                $result->add($this->getRepo()->getRefdataReference($outcome));
            }
        }
        return $result;
    }
}
