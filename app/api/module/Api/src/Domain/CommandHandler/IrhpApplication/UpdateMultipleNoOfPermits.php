<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Domain\Command\IrhpApplication\RegenerateIssueFee as RegenerateIssueFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\IrhpApplication\RegenerateApplicationFee as RegenerateApplicationFeeCmd;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateMultipleNoOfPermits as UpdateMultipleNoOfPermitsCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\MaxStockPermitsByApplication;
use RuntimeException;

/**
 * Update multiple no of permits
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class UpdateMultipleNoOfPermits extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'IrhpApplication';

    protected $extraRepos = ['IrhpPermitApplication'];

    /**
     * Handle command
     *
     * @param UpdateMultipleNoOfPermitsCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $irhpApplicationId = $command->getId();
        $irhpApplicationRepo = $this->getRepo();
        $irhpApplication = $irhpApplicationRepo->fetchById($irhpApplicationId);

        if (!$irhpApplication->isReadyForNoOfPermits()) {
            throw new ForbiddenException('IRHP application is not ready for number of permits');
        }

        $irhpApplication->storeFeesRequired();

        $irhpPermitApplicationRepo = $this->getRepo('IrhpPermitApplication');
        $rows = $irhpPermitApplicationRepo->getByIrhpApplicationWithStockInfo($command->getId());
        $permitsRequiredData = $command->getPermitsRequired();

        $response = $this->handleQuery(
            MaxStockPermitsByApplication::create(['id' => $irhpApplicationId])
        );
        $maxStockPermits = $response['result'];

        $irhpApplicationTypeId = $irhpApplication->getIrhpPermitType()->getId();
        foreach ($rows as $row) {
            $stockId = $row['stockId'];
            $permitsRequired = 0;
            $maxPermits = $maxStockPermits[$stockId];

            if ($maxPermits > 0) {
                $permitsRequired = match ($irhpApplicationTypeId) {
                    IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL => $this->deriveMultilateralPermitsRequired($row, $permitsRequiredData),
                    default => throw new RuntimeException('Unsupported permit type ' . $irhpApplicationTypeId),
                };

                if (($permitsRequired < 0) || ($permitsRequired > $maxPermits)) {
                    throw new RuntimeException(
                        sprintf(
                            'Out of range data for stock id %s - expected range 0 to %d but received %d',
                            $stockId,
                            $maxPermits,
                            $permitsRequired
                        )
                    );
                }
            }

            $irhpPermitApplication = $row['irhpPermitApplication'];
            $irhpPermitApplication->updatePermitsRequired($permitsRequired);
            $irhpPermitApplicationRepo->saveOnFlush($irhpPermitApplication);
        }

        $irhpApplication->resetCheckAnswersAndDeclaration();
        $irhpApplicationRepo->saveOnFlush($irhpApplication);
        $irhpPermitApplicationRepo->flushAll();

        if ($irhpApplication->haveFeesRequiredChanged()) {
            $this->result->merge(
                $this->handleSideEffects(
                    [
                        RegenerateApplicationFeeCmd::create(['id' => $irhpApplicationId]),
                        RegenerateIssueFeeCmd::create(['id' => $irhpApplicationId])
                    ]
                )
            );
        }

        $this->result->addId('irhpApplication', $irhpApplicationId);
        $this->result->addMessage(
            sprintf(
                'Updated %d required permit counts for IRHP application',
                count($rows)
            )
        );

        return $this->result;
    }

    /**
     * Retrieve the number of permits required from the multilateral form data array
     *
     *
     * @return int
     *
     * @throws RuntimeException
     */
    private function deriveMultilateralPermitsRequired(array $row, array $permitsRequiredData)
    {
        $validToTimestamp = strtotime((string) $row['validTo']);
        $year = date('Y', $validToTimestamp);

        if (isset($permitsRequiredData[$year]) && is_numeric($permitsRequiredData[$year])) {
            return intval($permitsRequiredData[$year]);
        }

        throw new RuntimeException(
            sprintf('Missing data or incorrect type for year %s', $year)
        );
    }
}
