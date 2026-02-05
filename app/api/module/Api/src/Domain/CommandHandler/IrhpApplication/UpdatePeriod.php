<?php

/**
 * Update Period selection (create irhp permit application for selected stock period)
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Command\IrhpPermitApplication\CreateForIrhpApplication;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermitApplication\UpdateIrhpPermitWindow;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\ApplicationAnswersClearer;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdatePeriod as UpdatePeriodCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

class UpdatePeriod extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'IrhpApplication';

    protected $extraRepos = ['IrhpPermitWindow', 'IrhpPermitApplication', 'IrhpPermitStock'];

    /** @var ApplicationAnswersClearer */
    private $applicationAnswersClearer;

    /**
     * Handle command
     *
     * @param UpdatePeriodCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $irhpApplicationId = $command->getId();

        /* @var $irhpApplication IrhpApplication */
        $irhpApplication = $this->getRepo()->fetchById($irhpApplicationId);

        /* @var $irhpPermitStock IrhpPermitStock */
        $irhpPermitStock = $this->getRepo('IrhpPermitStock')->fetchById($command->getIrhpPermitStock());

        /* @var $irhpPermitApplicationForCountry IrhpPermitApplication|null */
        $irhpPermitApplicationForCountry = null;
        $irhpPermitAppId = $irhpApplication->getIrhpPermitApplicationIdForCountry($irhpPermitStock->getCountry());

        if ($irhpPermitAppId) {
            $irhpPermitApplicationForCountry = $this->getRepo('IrhpPermitApplication')->fetchById($irhpPermitAppId);
        }

        $periodWindow = $this->getRepo('IrhpPermitWindow')->fetchLastOpenWindowByStockId($command->getIrhpPermitStock());

        if (empty($irhpPermitApplicationForCountry)) {
            $this->result->merge(
                $this->handleSideEffect(
                    CreateForIrhpApplication::create([
                        'irhpApplication' => $irhpApplication->getId(),
                        'irhpPermitWindow' => $periodWindow->getId(),
                    ])
                )
            );
            $irhpPermitApplicationId = $this->result->getIds()['irhpPermitApplication'];
        } elseif ($periodWindow->getId() != $irhpPermitApplicationForCountry->getIrhpPermitWindow()->getId()) {
            $this->applicationAnswersClearer->clear($irhpPermitApplicationForCountry);

            $this->result->merge(
                $this->handleSideEffect(
                    UpdateIrhpPermitWindow::create([
                        'id' => $irhpPermitApplicationForCountry->getId(),
                        'irhpPermitWindow' => $periodWindow->getId(),
                    ])
                )
            );
            $irhpPermitApplicationId = $this->result->getIds()['irhpPermitApplication'];
        } else {
            $irhpPermitApplicationId = $irhpPermitApplicationForCountry->getId();
        }

        $this->result->addId('irhpPermitApplication', $irhpPermitApplicationId);
        $this->result->addMessage('IrhpPermitApplication for selected stock period linked');

        $this->result->addId('irhpApplication', $irhpApplicationId);
        $this->result->addMessage('Period selection completed for IRHP application');

        return $this->result;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->applicationAnswersClearer = $container->get('QaApplicationAnswersClearer');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
