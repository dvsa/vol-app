<?php

/**
 * Grant Interim
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\InForceInterim as InForceInterimCmd;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\Application\GrantInterim as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateApplicationFee as CreateApplicationFeeCmd;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;

/**
 * Grant Interim
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GrantInterim extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['Fee'];

    public const string ACTION_GRANTED = 'granted';
    public const string ACTION_IN_FORCE = 'in_force';
    public const string ACTION_FEE_REQUEST = 'fee_request';

    /**
     * @param Cmd $command
     *
     * @return Result result result
     *
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command);

        $existingFees = $this->getExistingFees($application);
        $paidFees = $this->getPaidFees($application);
        $variationFees = $this->getRepo('Fee')->fetchFeeByTypeAndApplicationId(
            FeeType::FEE_TYPE_VAR,
            $application->getId()
        );
        $isVariation = $application->isVariation();
        $latestFee = null;

        // if we had paid fees before interim status can be set to in-force
        if (count($paidFees)) {
            $this->result->merge($this->handleSideEffect(InForceInterimCmd::create(['id' => $application->getId()])));
            $this->result->addId('action', self::ACTION_IN_FORCE);
            return $this->result;
        }

        // if there is no fees - we need to create one
        if (!empty($existingFees)) {
            $latestFee = $existingFees[0];
        } elseif (!$isVariation || ($isVariation && !empty($variationFees))) {
            $data = [
                'id' => $application->getId(),
                'feeTypeFeeType' => FeeType::FEE_TYPE_GRANTINT
            ];
            $feeResult = $this->handleSideEffect(CreateApplicationFeeCmd::create($data));
            $this->result->merge($feeResult);
            $latestFee = $this->getRepo('Fee')->fetchById($feeResult->getId('fee'));
        }

        if ($latestFee !== null) {
            // prepare fee request document and change status to granted
            $this->generateInterimFeeRequestDocument($application, $latestFee);
        }

        $application->setInterimStatus(
            $this->getRepo()->getRefdataReference(ApplicationEntity::INTERIM_STATUS_GRANTED)
        );

        $this->result->addMessage('Interim status updated');
        $this->getRepo()->save($application);
        $this->result->addId('action', $latestFee === null ? self::ACTION_GRANTED : self::ACTION_FEE_REQUEST);

        return $this->result;
    }

    /**
     * Get existing grant fees
     *
     *
     * @return array
     */
    private function getExistingFees(ApplicationEntity $application)
    {
        return $this->getRepo('Fee')->fetchInterimFeesByApplicationId($application->getId(), true);
    }

    /**
     * Get paid grant fees
     *
     *
     * @return array
     */
    private function getPaidFees(ApplicationEntity $application)
    {
        return $this->getRepo('Fee')->fetchInterimFeesByApplicationId($application->getId(), false, true);
    }

    private function generateInterimFeeRequestDocument(ApplicationEntity $application, Fee $fee)
    {
        if ($application->isVariation()) {
            $description = 'GV Interim direction fee request';
        } else {
            $description = 'GV Interim licence fee request';
        }

        $this->result->merge($this->generateDocument($application, $fee, $description));
    }

    protected function generateDocument(ApplicationEntity $application, Fee $fee, $description)
    {
        $dtoData = [
            'template' => 'FEE_REQ_INT_APP',
            'query' => [
                'application' => $application->getId(),
                'licence' => $application->getLicence()->getId(),
                'fee' => $fee->getId()
            ],
            'description' => $description,
            'application' => $application->getId(),
            'licence' => $application->getLicence()->getId(),
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS,
            'isExternal' => false,
            'isScan' => false,
            'dispatch' => true
        ];

        return $this->handleSideEffect(GenerateAndStore::create($dtoData));
    }
}
