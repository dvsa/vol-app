<?php

/**
 * Update Licence Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\LicenceOperatingCentre;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\LicenceOperatingCentre\Update as Cmd;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Psr\Container\ContainerInterface;

/**
 * Update Licence Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class Update extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'LicenceOperatingCentre';

    protected $extraRepos = ['Document', 'OperatingCentre'];

    /**
     * @var \Dvsa\Olcs\Api\Domain\Service\OperatingCentreHelper
     */
    protected $helper;

    /**
     * @param Cmd $command
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var LicenceOperatingCentre $loc */
        $loc = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $licence = $loc->getLicence();

        $this->helper->validate($licence, $command, $this->isGranted(Permission::SELFSERVE_USER), $loc);

        $operatingCentre = $loc->getOperatingCentre();

        $data = $command->getAddress();

        $this->result->merge($this->handleSideEffect(SaveAddress::create($data)));

        // Link, unlinked documents to the OC
        $this->helper->saveDocuments($licence, $operatingCentre, $this->getRepo('Document'));

        $this->helper->updateOperatingCentreLink(
            $loc,
            $licence,
            $command,
            $this->getRepo('LicenceOperatingCentre')
        );

        return $this->result;
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->helper = $container->get('OperatingCentreHelper');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
