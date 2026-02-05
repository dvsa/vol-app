<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Cli\Domain\Command\Permits\ApplyRangesToSuccessfulPermitApplications as ApplyRangesToSuccessfulPermitApplicationsCommand;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\StockBasedForCpProviderFactory;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * Apply ranges to successful permit applications
 * See https://wiki.i-env.net/display/olcs/Batch+Process%3A+Identify+Successful+Permit+Applications+with+Restricted+Countries
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ApplyRangesToSuccessfulPermitApplications extends ScoringCommandHandler implements TransactionedInterface
{
    public const ENTITY_KEY = 'entity';
    public const COUNTRY_IDS_KEY = 'countryIds';
    public const EMISSIONS_CATEGORY_KEY = 'emissionsCategory';
    public const PERMITS_REMAINING_KEY = 'permitsRemaining';

    protected $repoServiceName = 'IrhpCandidatePermit';

    protected $extraRepos = ['IrhpPermit', 'IrhpPermitRange', 'IrhpApplication'];

    /** @var StockBasedForCpProviderFactory */
    private $stockBasedForCpProviderFactory;

    /** @var array */
    private $ranges;

    /**
     * Handle command
     *
     * @param CommandInterface|ApplyRangesToSuccessfulPermitApplicationsCommand $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $stockId = $command->getStockId();

        $forCpProvider = $this->stockBasedForCpProviderFactory->create($stockId);

        $this->populateRanges(
            $this->getRepo('IrhpPermitRange')->getByStockId($stockId)
        );

        $candidatePermits = $this->getRepo('IrhpApplication')->getSuccessfulScoreOrderedInScope($stockId);

        $this->profileMessage('apply ranges to successful permit applications...');

        foreach ($candidatePermits as $candidatePermit) {
            $message = sprintf(
                '  - processing candidate permit %d with randomised score %s',
                $candidatePermit->getId(),
                $candidatePermit->getRandomizedScore()
            );
            $this->result->addMessage($message);

            $selectedRangeEntity = $forCpProvider->selectRange(
                $this->result,
                $candidatePermit,
                $this->ranges
            );

            $message = sprintf(
                '    - assigned range id %d to candidate permit %d',
                $selectedRangeEntity->getId(),
                $candidatePermit->getId()
            );
            $this->result->addMessage($message);

            $candidatePermit->applyRange($selectedRangeEntity);
            $this->getRepo()->saveOnFlush($candidatePermit);
            $this->decrementRangeStock($selectedRangeEntity);
        }

        $this->profileMessage('flushing apply ranges...');

        $this->getRepo()->flushAll();

        return $this->result;
    }

    /**
     * Populate the ranges instance variable with an array, with each element representing one range and consisting of
     * a reference to the range entity, a list of restricted country ids permitted by this range, the applicable
     * emissions category and the count of free permit numbers remaining within this range
     *
     * @param array $rangeEntities An array of Range entities
     *
     * @throws RuntimeException
     */
    private function populateRanges(array $rangeEntities)
    {
        $this->ranges = [];
        foreach ($rangeEntities as $rangeEntity) {
            $allocatedPermitsInRange = $this->getRepo('IrhpPermit')->getPermitCountByRange($rangeEntity->getId());
            $permitsRemainingInRange = $rangeEntity->getSize() - $allocatedPermitsInRange;

            if ($permitsRemainingInRange > 0) {
                $this->ranges[] = [
                    self::ENTITY_KEY => $rangeEntity,
                    self::COUNTRY_IDS_KEY => $this->extractIdsFromEntities(
                        $rangeEntity->getCountrys()->getValues()
                    ),
                    self::EMISSIONS_CATEGORY_KEY => $rangeEntity->getEmissionsCategory()->getId(),
                    self::PERMITS_REMAINING_KEY => $permitsRemainingInRange
                ];
            } elseif ($permitsRemainingInRange < 0) {
                throw new RuntimeException(
                    'Assertion failed in method ' . __METHOD__ . ': $permitsRemainingInRange < 0'
                );
            }
        }
    }

    /**
     * Decrement the count of permits available within a given range following a permit being assigned to that range
     *
     * @param array $selectedRange
     */
    private function decrementRangeStock(IrhpPermitRange $irhpPermitRange)
    {
        $idToDecrement = $irhpPermitRange->getId();

        $updatedRanges = [];
        foreach ($this->ranges as &$range) {
            $rangeEntity = $range[self::ENTITY_KEY];
            if ($rangeEntity->getId() == $idToDecrement) {
                $range[self::PERMITS_REMAINING_KEY]--;
                $message = sprintf(
                    '    - decrementing stock in range %d, stock is now %d',
                    $idToDecrement,
                    $range[self::PERMITS_REMAINING_KEY]
                );
                $this->result->addMessage($message);
            }
            if ($range[self::PERMITS_REMAINING_KEY] > 0) {
                $updatedRanges[] = $range;
            } else {
                $message = sprintf('    - stock in range %d is now exhausted', $idToDecrement);
                $this->result->addMessage($message);
            }
        }

        $this->ranges = $updatedRanges;
    }

    /**
     * Derive an array of numeric ids by calling getId on an array of entities
     *
     *
     * @return array
     */
    private function extractIdsFromEntities(array $entities)
    {
        $ids = [];

        foreach ($entities as $entity) {
            $ids[] = $entity->getId();
        }

        return $ids;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->stockBasedForCpProviderFactory = $container->get(
            'PermitsApplyRangesStockBasedForCpProviderFactory'
        );
        return parent::__invoke($container, $requestedName, $options);
    }
}
