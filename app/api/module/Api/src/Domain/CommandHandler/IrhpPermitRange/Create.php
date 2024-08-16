<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitRange;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange as RangeEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\IrhpPermitRange\Create as CreateRangeCmd;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Create an IRHP Permit Range
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
final class Create extends AbstractCommandHandler
{
    use IrhpPermitRangeOverlapTrait;

    protected $repoServiceName = 'IrhpPermitRange';
    protected $extraRepos = ['IrhpPermitStock', 'Country'];

    /**
     * Handle command
     *
     * @param CreateRangeCmd $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command): Result
    {
        $numberOfOverlappingRanges = $this->numberOfOverlappingRanges(
            $command->getIrhpPermitStock(),
            $command->getPrefix(),
            $command->getFromNo(),
            $command->getToNo()
        );

        if ($numberOfOverlappingRanges !== 0) {
            throw new ValidationException(['This Permit Number Range overlaps with another for this stock with the same prefix']);
        }

        $permitStock = $this->getRepo('IrhpPermitStock')->fetchById($command->getIrhpPermitStock());
        $permitType = $permitStock->getIrhpPermitType();

        if (
            ($permitType->isEcmtShortTerm() || $permitType->isEcmtAnnual())
            && $command->getEmissionsCategory() == RefData::EMISSIONS_CATEGORY_NA_REF
        ) {
            throw new ValidationException(['Emissions Category: N/A not valid for Short-term/Annual ECMT Stock']);
        }

        if ($permitType->isBilateral() && !$command->getJourney()) {
            throw new ValidationException(['Journey type is required.']);
        }

        $countrys = [];
        foreach ($command->getRestrictedCountries() as $country) {
            $countrys[] = $this->getRepo('Country')->getReference(Country::class, $country);
        }

        /**
         * @var CreateRangeCmd $command
         */
        $range = RangeEntity::create(
            $permitStock,
            $this->refData($command->getEmissionsCategory()),
            $command->getPrefix(),
            $command->getFromNo(),
            $command->getToNo(),
            $command->getSsReserve(),
            $command->getIsLostReplacement(),
            $countrys,
            $this->refDataOrNull($command->getJourney()),
            $command->getCabotage()
        );

        $this->getRepo()->save($range);

        $this->result->addId('IrhpPermitRange', $range->getId());
        $this->result->addMessage("IRHP Permit Range '{$range->getId()}' created");

        return $this->result;
    }
}
