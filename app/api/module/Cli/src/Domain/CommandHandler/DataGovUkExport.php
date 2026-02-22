<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler;

use Aws\S3\S3Client;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Domain\Command\Email\SendPsvOperatorListReport;
use Dvsa\Olcs\Api\Domain\Command\Email\SendInternationalGoods as SendIntlGoodsEmailCmd;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository;

/**
 * Export data to csv files for data.gov.uk
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
final class DataGovUkExport extends AbstractDataExport
{
    use QueueAwareTrait;

    public const ERR_INVALID_REPORT = 'Invalid report name';
    public const ERR_NO_TRAFFIC_AREAS = 'Traffic areas is empty';

    public const string OPERATOR_LICENCE = 'operator-licence';
    public const string BUS_REGISTERED_ONLY = 'bus-registered-only';
    public const string BUS_VARIATION = 'bus-variation';
    public const string PSV_OPERATOR_LIST = 'psv-operator-list';
    public const string INTERNATIONAL_GOODS = 'international-goods';

    public const FILE_DATETIME_FORMAT = 'Ymd_His';

    /**
     * @var string
     */
    protected $repoServiceName = 'DataGovUk';

    /**
     * @var string
     */
    private $reportName;

    /**
     * @var Repository\DataGovUk
     */
    private $dataGovUkRepo;

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Cli\Domain\Command\DataGovUkExport $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->reportName = $command->getReportName();
        $this->dataGovUkRepo = $this->getRepo();

        if ($this->reportName === self::OPERATOR_LICENCE) {
            $this->processOperatorLicences();
        } elseif ($this->reportName === self::BUS_REGISTERED_ONLY) {
            $this->processBusRegOnly();
        } elseif ($this->reportName === self::BUS_VARIATION) {
            $this->processBusVariation();
        } elseif ($this->reportName === self::PSV_OPERATOR_LIST) {
            $this->processPsvOperatorList();
        } elseif ($this->reportName === self::INTERNATIONAL_GOODS) {
            $this->processInternationalGoodsList();
        } else {
            throw new \Exception(self::ERR_INVALID_REPORT);
        }

        return $this->result;
    }

    /**
     * Process PSV Operator list and email
     *
     * @return Result
     */
    private function processPsvOperatorList()
    {
        $this->result->addMessage('Fetching data from DB for PSV Operators');
        $stmt = $this->dataGovUkRepo->fetchPsvOperatorList();

        $csvContent = $this->makeCsvForPsvOperatorList($stmt);

        $document = $this->handleSideEffect(
            $this->generatePsvOperatorListDocumentCmd($csvContent)
        );

        // Send email
        $emailQueue = $this->emailQueue(
            SendPsvOperatorListReport::class,
            ['id' => $document->getId('document')],
            $document->getId('document')
        );

        $tempFilePath = tempnam(sys_get_temp_dir(), 'psv_operator_list_');
        file_put_contents($tempFilePath, $csvContent);
        $this->uploadToS3($tempFilePath);
        unlink($tempFilePath);

        $email = $this->handleSideEffect($emailQueue);
        $this->result->merge($email);

        return $this->result;
    }

    /**
     * Process international goods list and email
     *
     * @return Result
     */
    private function processInternationalGoodsList()
    {
        $this->result->addMessage('Fetching data for international goods list');

        /** @var Repository\Licence $repo */
        $repo = $this->getRepo('Licence');
        $dbalResult = $repo->internationalGoodsReport();
        $csvPath = $this->singleCsvFromDbalResult($dbalResult, 'international_goods');
        $csvContent = file_get_contents($csvPath);

        $document = $this->handleSideEffect(
            $this->generateInternationalGoodsDocumentCmd($csvContent)
        );

        // Send email
        $emailQueue = $this->emailQueue(
            SendIntlGoodsEmailCmd::class,
            ['id' => $document->getId('document')],
            $document->getId('document')
        );

        $this->uploadToS3($csvPath);

        unlink($csvPath);

        $email = $this->handleSideEffect($emailQueue);
        $this->result->merge($email);

        return $this->result;
    }

    /**
     * Creates a command to upload the international goods csv
     *
     * @param string $document document content
     *
     * @return UploadCmd
     */
    private function generateInternationalGoodsDocumentCmd($document)
    {
        $data = [
            'content' => base64_encode($document),
            'category' => Category::CATEGORY_REPORT,
            'subCategory' => SubCategory::REPORT_SUB_CATEGORY_GV,
            'filename' => 'international-goods-list.csv',
            'description' => 'International goods list ' . date('d/m/Y'),
            'user' => IdentityProviderInterface::SYSTEM_USER,
        ];

        return UploadCmd::create($data);
    }

    /**
     * Creates a command to upload the PSV Operator list CSV file
     *
     * @param string $document document content
     *
     * @return UploadCmd
     */
    private function generatePsvOperatorListDocumentCmd($document)
    {
        $data = [
            'content' => base64_encode($document),
            'category' => Category::CATEGORY_REPORT,
            'subCategory' => SubCategory::REPORT_SUB_CATEGORY_PSV,
            'filename' => 'psv-operator-list.csv',
            'description' => 'PSV Operator list',
            'user' => IdentityProviderInterface::SYSTEM_USER,
        ];

        return UploadCmd::create($data);
    }

    /**
     * Process operator licences
     *
     * @return void
     */
    private function processOperatorLicences()
    {
        $areas = array_map(
            fn(TrafficAreaEntity $item) => $item->getName(),
            $this->getTrafficAreas()
        );

        $this->result->addMessage('Fetching data from DB for Operator Licences');
        $dbalResult = $this->dataGovUkRepo->fetchOperatorLicences($areas);
        $this->makeCsvsFromDbalResult($dbalResult, 'GeographicRegion', 'OLBSLicenceReport');
    }

    /**
     * Process Bus Reg Only
     *
     * @return void
     */
    private function processBusRegOnly()
    {
        $areas = array_map(
            fn(TrafficAreaEntity $item) => $item->getId(),
            $this->getTrafficAreas()
        );

        $this->result->addMessage('Fetching data from DB for Bus Registered Only');
        $dbalResult = $this->dataGovUkRepo->fetchBusRegisteredOnly($areas);
        $this->makeCsvsFromDbalResult($dbalResult, 'Current Traffic Area', 'Bus_RegisteredOnly');
    }

    /**
     * Process Bus Variation
     *
     * @return void
     */
    private function processBusVariation()
    {
        $areas = array_map(
            fn(TrafficAreaEntity $item) => $item->getId(),
            $this->getTrafficAreas()
        );

        $this->result->addMessage('Fetching data from DB for Bus Variation');
        $dbalResult = $this->dataGovUkRepo->fetchBusVariation($areas);
        $this->makeCsvsFromDbalResult($dbalResult, 'Current Traffic Area', 'Bus_Variation');
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $exportCfg = $config['data-gov-uk-export'] ?? [];

        if (isset($exportCfg['s3_uri'])) {
            $parsedUrl = parse_url((string) $exportCfg['s3_uri']);
            $this->s3Bucket = $parsedUrl['host'];
            $this->path = ltrim($parsedUrl['path'], '/');
        }

        $this->s3Client = $container->get(S3Client::class);

        return parent::__invoke($container, $requestedName, $options);
    }
}
