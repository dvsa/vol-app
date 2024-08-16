<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Query\Permits\DeviationData as DeviationDataQuery;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Initialise Scope
 */
class InitialiseScope extends ScoringCommandHandler
{
    protected $repoServiceName = 'IrhpCandidatePermit';

    protected $extraRepos = ['IrhpApplication'];

   /**
    * Handle command
    *
    * @param CommandInterface $command command
    *
    * @return Result
    */
    public function handleCommand(CommandInterface $command)
    {
        $candidatePermitRepo = $this->getRepo();
        $irhpApplicationRepo = $this->getRepo('IrhpApplication');

        $stockId = $command->getStockId();

        $this->profileMessage('clear scope...');
        $irhpApplicationRepo->clearScope($stockId);

        $this->profileMessage('apply scope...');
        $irhpApplicationRepo->applyScope($stockId);

        $this->profileMessage('fetch deviation source values...');
        $candidatePermitSourceValues = $irhpApplicationRepo->fetchDeviationSourceValues($stockId);

        $totalPermitCount = count($candidatePermitSourceValues);
        $randomizedScoreCount = 0;

        if ($totalPermitCount > 0) {
            $this->profileMessage('get deviation data...');
            $deviationData = $this->handleQuery(
                DeviationDataQuery::create(
                    ['sourceValues' => $candidatePermitSourceValues]
                )
            );

            $deviation = $command->getDeviation();
            if (!is_null($deviation)) {
                $deviationData['meanDeviation'] = $deviation;
                $this->result->addMessage('using manually overridden mean deviation of ' . $deviation);
            } else {
                $this->result->addMessage('using computed mean deviation of ' . $deviationData['meanDeviation']);
            }

            $this->profileMessage('update candidate permits individually...');
            foreach ($candidatePermitSourceValues as $sourceValue) {
                $candidatePermit = $candidatePermitRepo->fetchById($sourceValue['candidatePermitId']);
                $candidatePermit->prepareForScoring();

                if (!$candidatePermit->hasRandomizedScore()) {
                    $candidatePermit->applyRandomizedScore($deviationData, $sourceValue['licNo']);
                    $randomizedScoreCount++;
                }

                $candidatePermitRepo->saveOnFlush($candidatePermit);
            }

            $this->profileMessage('flush randomised scores...');
            $candidatePermitRepo->flushAll();
        }

        $this->result->addMessage('Established scope of candidate permits');
        $this->result->addMessage('    - Candidate permits in scope: ' . $totalPermitCount);
        $this->result->addMessage('    - Randomised scores set: ' . $randomizedScoreCount);

        return $this->result;
    }
}
