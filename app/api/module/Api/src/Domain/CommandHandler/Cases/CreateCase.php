<?php

/**
 * Create a case
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Transfer\Command\Cases\CreateCase as CreateCaseCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Create Case
 */
final class CreateCase extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Cases';

    /**
     * Creates a case
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var CreateCaseCmd $command */

        $result = new Result();

        $caseType = $this->getRepo()->getRefdataReference($command->getCaseType());
        $categorys = $this->processCategorys($command->getCategorys());
        $outcomes = $this->processOutcomes($command->getOutcomes());

        $application = (is_numeric($command->getApplication())
            ? $this->getRepo()->getReference(Application::class, $command->getApplication()) : null);
        $licence = (is_numeric($command->getLicence())
            ? $this->getRepo()->getReference(Licence::class, $command->getLicence()) : null);
        $transportManager = (is_numeric($command->getTransportManager())
            ? $this->getRepo()->getReference(TransportManager::class, $command->getTransportManager()) : null);

        $case = new Cases(
            new \DateTime(),
            $caseType,
            $categorys,
            $outcomes,
            $application,
            $licence,
            $transportManager,
            $command->getEcmsNo(),
            $command->getDescription()
        );

        $this->getRepo()->save($case);
        $result->addMessage('Case created');
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
