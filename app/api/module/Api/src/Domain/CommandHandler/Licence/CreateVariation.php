<?php

/**
 * Create Variation
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateFee;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepository;
use Dvsa\Olcs\Api\Domain\SlaCalculatorAwareInterface;
use Dvsa\Olcs\Api\Domain\SlaCalculatorAwareTrait;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Entity\Application\ApplicationTracking;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVariation as Cmd;

/**
 * Create Variation
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateVariation extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface, SlaCalculatorAwareInterface
{
    use AuthAwareTrait;
    use SlaCalculatorAwareTrait;

    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['Application', 'Sla'];

    /**
     * @param CommandInterface|Cmd $command The command
     *
     * @return Result
     * @throws ForbiddenException
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();
        $shouldUpdateApplicationCompletion = false;

        /** @var LicenceRepository $repository */
        $repository = $this->getRepo();

        /** @var Licence $licence */
        $licence = $repository->fetchUsingId($command);

        if ($licence->canHaveVariation() === false) {
            throw new ForbiddenException('Unable to create variation due to the licence status.');
        }

        if ($this->isGranted(Permission::INTERNAL_USER)) {
            $status = $repository->getRefdataReference(Application::APPLICATION_STATUS_UNDER_CONSIDERATION);
        } else {
            $status = $repository->getRefdataReference(Application::APPLICATION_STATUS_NOT_SUBMITTED);
        }

        $variation = new Application($licence, $status, true);
        $variation->copyInformationFromLicence($licence);

        $applicationCompletion = new ApplicationCompletion($variation);
        $variation->setApplicationCompletion($applicationCompletion);

        $applicationTracking = new ApplicationTracking($variation);
        $variation->setApplicationTracking($applicationTracking);

        if ($command->getLicenceType() !== null) {
            $variation->setLicenceType($repository->getRefdataReference($command->getLicenceType()));
            $shouldUpdateApplicationCompletion = true;
        }

        if ($this->isGranted(Permission::INTERNAL_USER) && $command->getReceivedDate() !== null) {
            $variation->setReceivedDate(new DateTime($command->getReceivedDate()));
            $this->setTargetCompletionDateForApplication($variation);
        }

        if ($command->getVariationType() !== null) {
            $variation->setVariationType($repository->getRefdataReference($command->getVariationType()));
        }

        if ($this->isGranted(Permission::SELFSERVE_USER)) {
            $variation->setAppliedVia($repository->getRefdataReference(Application::APPLIED_VIA_SELFSERVE));
        }
        if ($this->isGranted(Permission::INTERNAL_USER)) {
            $variation->setAppliedVia($repository->getRefdataReference($command->getAppliedVia()));
        }

        $this->getRepo('Application')->save($variation);

        $result->addId('application', $variation->getId());
        $result->addMessage('Variation created');

        if ($shouldUpdateApplicationCompletion) {
            $data = ['id' => $variation->getId(), 'section' => 'typeOfLicence'];
            $result->merge($this->handleSideEffect(UpdateApplicationCompletion::create($data)));
        }

        if ($this->isGranted(Permission::INTERNAL_USER) && $command->getFeeRequired() == 'Y') {
            $data = ['id' => $variation->getId(), 'feeTypeFeeType' => FeeType::FEE_TYPE_VAR];
            $result->merge($this->handleSideEffect(CreateFee::create($data)));
        }

        return $result;
    }
}
