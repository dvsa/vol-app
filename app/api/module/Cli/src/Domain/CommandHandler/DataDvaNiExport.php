<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler;

use Aws\S3\S3Client;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Domain\Repository;

/**
 * Export data to csv files for data.gov.uk
 *
 */
final class DataDvaNiExport extends AbstractDataExport
{
    use QueueAwareTrait;

    public const FILE_DATETIME_FORMAT = 'YmdHis';
    public const string NI_OPERATOR_LICENCE = 'ni-operator-licence';


    /**
     * @var string
     */
    protected $repoServiceName = 'DataDvaNi';

    /**
     * @var string
     */
    private $reportName;

    /**
     * @var Repository\DataDvaNi
     */
    private $dataDvaNiRepo;

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Cli\Domain\Command\DataDvaNiExport $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->reportName = $command->getReportName();

        $this->dataDvaNiRepo = $this->getRepo();

        if ($this->reportName === self::NI_OPERATOR_LICENCE) {
            $this->processNiOperatorLicences();
        } else {
            throw new \Exception(self::ERR_INVALID_REPORT);
        }

        return $this->result;
    }

    /**
     * Process Nothern Ireland operator licences
     *
     * @return void
     */
    private function processNiOperatorLicences()
    {
        $this->result->addMessage('Fetching data from DB for NI Operator Licences');
        $dbalResult = $this->dataDvaNiRepo->fetchNiOperatorLicences();

        $csvFilePath = $this->singleCsvFromDbalResult($dbalResult, 'NiGvLicences', '-');

        $manifestPath = $this->createManifest([$csvFilePath]);

        $archivePath = $this->createTarGzArchive([$csvFilePath], $manifestPath);

        $this->uploadToS3($archivePath);

        $this->cleanUpFiles([$csvFilePath, $manifestPath, $archivePath]);
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $exportCfg = $config['data-dva-ni-export'] ?? [];

        if (isset($exportCfg['s3_uri'])) {
            $parsedUrl = parse_url(rtrim((string) $exportCfg['s3_uri'], '/'));
            $this->s3Bucket = $parsedUrl['host'];
            $this->path = ltrim($parsedUrl['path'], '/');
        }

        $this->s3Client = $container->get(S3Client::class);

        return parent::__invoke($container, $requestedName, $options);
    }
}
