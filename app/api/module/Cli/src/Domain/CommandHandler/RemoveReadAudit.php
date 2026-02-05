<?php

/**
 * Remove Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Cli\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Api\Domain\Repository\ReadAudit\ReadAuditRepositoryInterface;

/**
 * Remove Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class RemoveReadAudit extends AbstractCommandHandler
{
    protected $extraRepos = [
        'ApplicationReadAudit',
        'LicenceReadAudit',
        'CasesReadAudit',
        'BusRegReadAudit',
        'TransportManagerReadAudit',
        'OrganisationReadAudit',
    ];

    protected $maxAge = '1 year';

    public function handleCommand(CommandInterface $command)
    {
        $oldestDate = date('Y-m-d', strtotime('-' . $this->maxAge));

        foreach ($this->extraRepos as $repoName) {

            /** @var ReadAuditRepositoryInterface $repo */
            $repo = $this->getRepo($repoName);

            $effected = $repo->deleteOlderThan($oldestDate);

            $this->result->addMessage($effected . ' ' . $repoName . ' records older than ' . $oldestDate . ' removed');
        }

        return $this->result;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (isset($config['batch_config']['remove-read-audit']['max-age'])) {
            $this->maxAge = $config['batch_config']['remove-read-audit']['max-age'];
        }
        return parent::__invoke($container, $requestedName, $options);
    }
}
