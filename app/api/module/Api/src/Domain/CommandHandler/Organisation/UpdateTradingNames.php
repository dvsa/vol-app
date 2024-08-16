<?php

/**
 * Update Trading Names
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Organisation;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Doctrine\Common\Collections\Collection;
use Dvsa\Olcs\Api\Entity\Organisation\TradingName;

/**
 * Update Trading Names
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateTradingNames extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['Organisation', 'TradingName'];

    public function handleCommand(CommandInterface $command)
    {
        $fromLicence = $command->getLicence() !== null;

        if ($fromLicence) {
            /** @var Licence $licence */
            $licence = $this->getRepo()->fetchById($command->getLicence());
            $organisation = null;
            $current = $licence->getTradingNames();
        } else {
            $licence = null;
            /** @var Organisation $organisation */
            $organisation = $this->getRepo('Organisation')
                ->fetchById($command->getOrganisation());

            $criteria = Criteria::create();
            $criteria->where($criteria->expr()->isNull('licence'));

            $current = $organisation->getTradingNames()->matching($criteria);
        }

        if ($this->haveTradingNamesChanged($current, $command->getTradingNames())) {
            $this->result->setFlag('hasChanged', true);

            [$newCount, $unchangedCount, $removedCount] = $this->updateTradingNames(
                $current,
                $command->getTradingNames(),
                $organisation,
                $licence
            );

            if ($fromLicence) {
                $this->result->merge(
                    $this->clearLicenceCacheSideEffect($licence->getId())
                );
            } else {
                $this->result->merge(
                    $this->clearOrganisationCacheSideEffect($organisation->getId())
                );
            }

            $this->result->addMessage($newCount . ' new trading name(s)');
            $this->result->addMessage($unchangedCount . ' unchanged trading name(s)');
            $this->result->addMessage($removedCount . ' trading name(s) removed');
        } else {
            $this->result->setFlag('hasChanged', false);
            $this->result->addMessage('Trading names are unchanged');
        }

        return $this->result;
    }

    private function updateTradingNames(
        Collection $current,
        array $new,
        Organisation $organisation = null,
        Licence $licence = null
    ) {
        // Differentiate between trading names to keep and trading names to remove
        [$maintain, $remove] = $current->partition(
            function ($key, $tradingName) use (&$new) {
                $index = array_search($tradingName->getName(), $new);

                if ($index !== false) {
                    unset($new[$index]);

                    return true;
                }

                return false;
            }
        );

        $newCount = 0;
        $unchangedCount = $maintain->count();
        $removedCount = $remove->count();

        // Remove trading names
        foreach ($remove as $key => $entity) {
            $this->getRepo('TradingName')->delete($entity);
        }

        // Create new trading names
        foreach ($new as $name) {
            $newCount++;
            $this->createTradingName($name, $organisation, $licence);
        }

        return [$newCount, $unchangedCount, $removedCount];
    }

    private function createTradingName($name, Organisation $organisation = null, Licence $licence = null)
    {
        $tradingName = new TradingName($name, $organisation);

        if ($licence !== null) {
            $tradingName->setLicence($licence);
        }

        $this->getRepo('TradingName')->save($tradingName);
    }

    private function haveTradingNamesChanged(Collection $current, array $new)
    {
        // We don't have the same number, so we must have changes
        if ($current->count() != count($new)) {
            return true;
        }

        // Check if all new names exist
        foreach ($new as $name) {
            $matched = false;

            /** @var TradingName $tradingName */
            foreach ($current as $tradingName) {
                if ($tradingName->getName() === $name) {
                    $matched = true;
                    break;
                }
            }

            // We have at least 1 new trading name
            if ($matched === false) {
                return true;
            }
        }

        return false;
    }
}
