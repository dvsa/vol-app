<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Domain\CommandHandler;

use Doctrine\DBAL\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Cli\Service\Utils\ExportToCsv;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

/**
 * Export difference between company house and OLCS data to csv files
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
final class CompaniesHouseVsOlcsDiffsExport extends AbstractCommandHandler
{
    protected $repoServiceName = 'CompanyHouseVsOlcsDiffs';

    /** @var  string */
    private $path;

    /** @var Repository\CompaniesHouseVsOlcsDiffs */
    private $chVsOlcsDiffs;

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Cli\Domain\Command\CompaniesHouseVsOlcsDiffsExport $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->path = (trim($command->getPath()) ?: $this->path);

        $this->chVsOlcsDiffs = $this->getRepo();

        $this->processOrgNameDiff();
        $this->processOrgStatusNotActice();
        $this->processOrgAddressDiff();
        $this->processOrgOfficersDiff();

        return $this->result;
    }

    /**
     * create csv file with organisation officer (people) differences
     *
     * @return void
     */
    private function processOrgOfficersDiff(): void
    {
        $this->result->addMessage('Fetching data from DB for Company house and Organisation Officers differences');
        $dbalResult = $this->chVsOlcsDiffs->fetchOfficerDiffs();

        $this->makeCsvsFromDbalResult($dbalResult, 'CompanyOfficerDiffs');
    }

    /**
     * create csv file with organisation address differences
     *
     * @return void
     */
    private function processOrgAddressDiff(): void
    {
        $this->result->addMessage('Fetching data from DB for Company house and Organisation Address differences');
        $dbalResult = $this->chVsOlcsDiffs->fetchAddressDiffs();

        $this->makeCsvsFromDbalResult($dbalResult, 'CompanyAddressDiffs');
    }

    /**
     * create csv file with organisation whose name not match to name in companies house
     *
     * @return void
     */
    private function processOrgNameDiff(): void
    {
        $this->result->addMessage('Fetching data from DB for Company house and Organisation Name differences');
        $dbalResult = $this->chVsOlcsDiffs->fetchNameDiffs();

        $this->makeCsvsFromDbalResult($dbalResult, 'CompanyNameDiffs');
    }

    /**
     * create csv file with organisation which has 'not active' status in companies house
     *
     * @return void
     */
    private function processOrgStatusNotActice(): void
    {
        $this->result->addMessage('Fetching data from DB where Organisation not active in Company house');
        $dbalResult = $this->chVsOlcsDiffs->fetchWithNotActiveStatus();

        $this->makeCsvsFromDbalResult($dbalResult, 'CompanyNotActive');
    }

    /**
     * Fill csv files with data. Csv created by value of Key Field and File name.
     */
    private function makeCsvsFromDbalResult(Result $dbalResult, string $fileName): void
    {
        //  create csv file
        $filePath = $this->path . '/' . $fileName . '.csv';

        $this->result->addMessage('create csv file: ' . $filePath);
        $fh = ExportToCsv::createFile($filePath);

        //  add title & first row
        $row = $dbalResult->fetchAssociative();

        if ($row !== false) {
            fputcsv($fh, array_keys($row));
            fputcsv($fh, $row);
        }

        //  add rows
        while (($row = $dbalResult->fetchAssociative()) !== false) {
            //  add rows to csv from pool
            fputcsv($fh, $row);
        }

        //  close file
        fclose($fh);
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $exportCfg = (!empty($config['ch-vs-olcs-export']) ? $config['ch-vs-olcs-export'] : []);
        if (isset($exportCfg['path'])) {
            $this->path = $exportCfg['path'];
        }
        return parent::__invoke($container, $requestedName, $options);
    }
}
