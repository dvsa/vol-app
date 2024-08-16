<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Domain\Command\Licence\UpdateTotalCommunityLicences as UpdateTotalCommunityLicencesCmd;

/**
 * Update All Community Licences
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class UpdateAllCommunityLicences extends AbstractCommandHandler implements TransactionedInterface, CacheAwareInterface
{
    use CacheAwareTrait;

    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['CommunityLic'];

    protected $status; // override this

    public function handleCommand(CommandInterface $command)
    {
        /* @var $licence Licence */
        $licence = $this->getRepo()->fetchById($command->getId());

        $status = $this->getRepo()->getRefdataReference($this->status);

        $this->getRepo('CommunityLic')->expireAllForLicence($licence->getId(), $this->status);

        $this->result->addMessage(sprintf('Community licence(s) updated to %s', $status->getDescription()));

        $this->result->merge(
            $this->handleSideEffect(
                UpdateTotalCommunityLicencesCmd::create(['id' => $command->getId()])
            )
        );

        $this->clearLicenceCaches($licence);
        return $this->result;
    }
}
