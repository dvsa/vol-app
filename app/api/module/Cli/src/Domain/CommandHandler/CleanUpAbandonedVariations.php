<?php

/**
 * Clean up abandoned variations
 */

namespace Dvsa\Olcs\Cli\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\Variation\DeleteVariation;

/**
 * Remove abandoned variations
 */

final class CleanUpAbandonedVariations extends AbstractCommandHandler
{
    protected $repoServiceName = 'Application';

    protected $olderThan = '4 hours';

    /**
     * handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $olderThanDate = date('Y-m-d H:i:s', strtotime('-' . $this->olderThan));

        /** @var Application $repo */
        $repo = $this->getRepo();

        $abandonedVariations = $repo->fetchAbandonedVariations($olderThanDate);

        /* @var $variation ApplicationEntity */
        foreach ($abandonedVariations as $variation) {
            $this->handleSideEffect(DeleteVariation::create(['id' => $variation->getId()]));
            $this->result->addId('variation ' . $variation->getId(), $variation->getId());
        }

        $this->result->addMessage(count($abandonedVariations) . ' abandoned variation records deleted');

        return $this->result;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (isset($config['batch_config']['clean-abandoned-variations']['older-than'])) {
            $this->olderThan = $config['batch_config']['clean-abandoned-variations']['older-than'];
        }
        return parent::__invoke($container, $requestedName, $options);
    }
}
