<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Create VI Extract files
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateViExtractFiles extends AbstractCommandHandler
{
    public const string PHP_EOL_WIN = "\r\n";

    protected $extraRepos = [
        'ViOcView',
        'ViOpView',
        'ViTnmView',
        'ViVhlView'
    ];

    protected $exportPath = null;

    protected $paramMap = [
        'oc' => [
            'repo'   => 'ViOcView',
            'name'   => 'Operating Centres',
            'prefix' => 'tanopc',
            'params' => [
                'ocId'
            ],
            'method' => 'clearOcViIndicators'
        ],
        'op' => [
            'repo'   => 'ViOpView',
            'name'   => 'Operators',
            'prefix' => 'tanopo',
            'params' => [
                'licId'
            ],
            'method' => 'clearLicencesViIndicators'
        ],
        'tnm' => [
            'repo'   => 'ViTnmView',
            'name'   => 'Trading Names',
            'prefix' => 'tantnm',
            'params' => [
                'tradingNameId'
            ],
            'method' => 'clearTradingNamesViIndicators'
        ],
        'vhl' => [
            'repo'   => 'ViVhlView',
            'name'   => 'Vehicles',
            'prefix' => 'tanveh',
            'params' => [
                'vhlId',
                'licId'
            ],
            'method' => 'clearLicenceVehiclesViIndicators'
        ],
    ];

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        if ($command->getPath()) {
            $this->exportPath = $command->getPath();
        }
        foreach ($this->paramMap as $key => $settings) {
            $method = 'get' . ucfirst((string) $key);
            $commandParam = $command->$method();
            $repo = $this->getRepo($settings['repo']);
            $fileName = $this->getFilename($settings['prefix']);
            if (
                file_exists($fileName)
                && rename($fileName, $this->getBackupFilename($settings['prefix'])) === false
            ) {
                throw new \Exception(
                    'Error renaming record(s) for ' . $settings['name'] . ', please check the target path'
                );
            }
            if ($commandParam || $command->getAll()) {
                $results = $repo->fetchForExport();
                $total = count($results);
                $this->result->addMessage('Found ' . $total . ' record(s) for ' . $settings['name']);
                if ($total) {
                    foreach ($results as &$result) {
                        $result['line'] = strtoupper((string) $result['line']);
                    }
                    $content = implode(self::PHP_EOL_WIN, array_column($results, 'line')) . self::PHP_EOL_WIN;
                    if (file_put_contents($fileName, $content) === false) {
                        throw new \Exception(
                            'Error writing record(s) for ' . $settings['name'] . ', please check the target path'
                        );
                    }
                } else {
                    $content = self::PHP_EOL_WIN;
                    if (file_put_contents($fileName, $content) === false) {
                        throw new \Exception(
                            'Error writing empty file for ' . $settings['name'] . ', please check the target path'
                        );
                    }
                    $this->result->addMessage('Empty file written for ' . $settings['name']);
                    continue;
                }
                $this->clearViFlags($repo, $results, $key);
                $this->result->addMessage($total . ' record(s) saved for ' . $settings['name']);
                $this->result->addMessage('VI flags cleared');
            }
        }
        return $this->result;
    }

    /**
     * Clear VI flags
     *
     * @param mixed  $repo    repository
     * @param array  $results results
     * @param string $key     key
     *
     * @return void
     */
    protected function clearViFlags(mixed $repo, $results, $key)
    {
        $params = [];
        foreach ($results as $result) {
            $elements = [];
            foreach ($this->paramMap[$key]['params'] as $paramName) {
                $elements[$paramName] = $result[$paramName];
            }
            $params[] = $elements;
        }
        $repo->{$this->paramMap[$key]['method']}($params);
    }

    /**
     * Get filename
     *
     * @param string $prefix prefix
     *
     * @return string
     */
    protected function getFilename($prefix = '')
    {
        return $this->exportPath
            . '/'
            . $prefix
            . ((new DateTime())->format('YmdHis'))
            . '.dat';
    }

    /**
     * Get backup filename
     *
     * @param string $prefix prefix
     *
     * @return string
     */
    protected function getBackupFilename($prefix = '')
    {
        $dt = new DateTime();
        return $this->exportPath
            . '/'
            . $prefix
            . $dt->format('Ymd')
            . '_'
            . $dt->format('His')
            . '.bak';
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (isset($config['vi_extract_files']['export_path'])) {
            $this->exportPath = $config['vi_extract_files']['export_path'];
        }
        return parent::__invoke($container, $requestedName, $options);
    }
}
