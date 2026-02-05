<?php

/**
 * Generate Licence Number
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Licence\LicenceNoGen;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Doctrine\ORM\Query;
use Psr\Container\ContainerInterface;

/**
 * Generate Licence Number
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GenerateLicenceNumber extends AbstractCommandHandler
{
    protected $licNoGenRepo;

    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);
        $licence = $application->getLicence();

        $result = new Result();

        if ($application->getGoodsOrPsv() === null || $licence->getTrafficArea() === null) {
            $result->addMessage('Can\'t generate licence number');
            return $result;
        }

        if ($licence->getLicNo() === null) {
            $newLicNo = $this->getNewLicNo($licence, $application);

            $licence->setLicNo($newLicNo);

            $this->getRepo()->save($application);
            $result->addId('licenceNumber', $licence->getLicNo());
            $result->addMessage('Licence number generated');
            return $result;
        }

        $licNo = $licence->getLicNo();

        if (substr($licNo, 1, 1) != $licence->getTrafficArea()->getId()) {
            $licNo =  sprintf(
                '%s%s%s',
                substr($licNo, 0, 1),
                $licence->getTrafficArea()->getId(),
                substr($licNo, 2)
            );

            $licence->setLicNo($licNo);
            $this->getRepo()->save($application);
            $result->addId('licenceNumber', $licence->getLicNo());
            $result->addMessage('Licence number updated');
            return $result;
        }

        $result->addMessage('Licence number is unchanged');
        return $result;
    }

    private function getNewLicNo($licence, $application)
    {
        $licenceNoGen = new LicenceNoGen($licence);
        $this->licNoGenRepo->save($licenceNoGen);

        return sprintf(
            '%s%s%s',
            $application->getCategoryPrefix(),
            $licence->getTrafficArea()->getId(),
            $licenceNoGen->getId()
        );
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->licNoGenRepo = $container->get('RepositoryServiceManager')
            ->get('LicenceNoGen');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
