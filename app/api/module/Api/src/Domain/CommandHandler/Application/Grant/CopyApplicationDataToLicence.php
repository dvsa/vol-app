<?php

/**
 * Copy Application Data To Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Copy Application Data To Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CopyApplicationDataToLicence extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command);

        $this->copyDataToLicence($application);

        $this->getRepo()->save($application);

        return $result;
    }

    protected function copyDataToLicence(ApplicationEntity $application)
    {
        $licence = $application->getLicence();

        $now = new DateTime();
        $dom = $now->format('j');
        $expiry = new DateTime('+5 years -' . $dom . ' days');

        $licence->copyInformationFromApplication($application);

        $licence->setStatus($this->getRepo()->getRefdataReference(Licence::LICENCE_STATUS_VALID));
        $licence->setInForceDate($now);
        $licence->setReviewDate(new DateTime('+5 years'));
        $licence->setExpiryDate($expiry);
        $licence->setFeeDate($expiry);
    }
}
