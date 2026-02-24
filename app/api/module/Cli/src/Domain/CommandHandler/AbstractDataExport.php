<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Domain\CommandHandler;

use Aws\S3\S3Client;
use Doctrine\DBAL\Result;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Service\Exception;

/**
 * Abstract class to be used by Export data to csv files
 */
abstract class AbstractDataExport extends AbstractCommandHandler
{
    use QueueAwareTrait;

    public const ERR_INVALID_REPORT = 'Invalid report name';
    public const ERR_NO_TRAFFIC_AREAS = 'Traffic areas is empty';

    public const FILE_DATETIME_FORMAT = 'Ymd_His';

    /**
     * @var array
     */
    protected $extraRepos = [
        'TrafficArea',
        'SystemParameter',
        'Category',
        'SubCategory',
        'Licence'
    ];

    protected string $path;

    private array $csvPool = [];

    protected S3Client $s3Client;

    protected string $s3Bucket;

    /**
     * Fill a CSV with the result of a doctrine statement
     *
     * @param Result    $dbalResult        db records set
     * @param string    $fileName          main part of file name
     * @param string    $fileNameSeparator (optional) the separator between the main fileName and the timestamp
     *
     * @return string
     */
    protected function singleCsvFromDbalResult(Result $dbalResult, $fileName, $fileNameSeparator = '_')
    {
        $date = new DateTime('now');
        $fileBaseName = $fileName . $fileNameSeparator . $date->format(static::FILE_DATETIME_FORMAT) . '.csv';

        $tempCsvPath = sys_get_temp_dir() . '/' . $fileBaseName;
        $this->result->addMessage('Creating CSV file: ' . $tempCsvPath);
        $fh = fopen($tempCsvPath, 'w');

        $firstRow = false;

        while (($row = $dbalResult->fetchAssociative()) !== false) {
            if (!$firstRow) {
                fputcsv($fh, array_keys($row));
                $firstRow = true;
            }

            fputcsv($fh, $row);
        }

        fclose($fh);

        return $tempCsvPath;
    }

    /**
     * Fill csv files with data. Csv created by value of Key Field and File name.
     *
     * @param Result    $dbalResult db records set
     * @param string    $keyFld     name of Key field in data set
     * @param string    $fileName   main part of file name
     *
     * @return void
     */
    protected function makeCsvsFromDbalResult(Result $dbalResult, $keyFld, $fileName)
    {
        $filePaths = [];
        $fileHandles = [];

        // add rows
        while (($row = $dbalResult->fetchAssociative()) !== false) {
            $key = $row[$keyFld];

            if (!isset($fileHandles[$key])) {
                $fileBaseName = $fileName . '_' . $key . '.csv';
                $filePath = sys_get_temp_dir() . '/' . $fileBaseName;

                $this->result->addMessage('Creating CSV file: ' . $filePath);
                $fh = fopen($filePath, 'w');

                fputcsv($fh, array_keys($row));
                fputcsv($fh, $row);

                $fileHandles[$key] = $fh;
                $filePaths[$key] = $filePath;

                continue;
            }

            $fh = $fileHandles[$key];
            fputcsv($fh, $row);
        }

        foreach ($fileHandles as $key => $fh) {
            fclose($fh);
            $filePath = $filePaths[$key];
            $this->uploadToS3($filePath);
            unlink($filePath);
        }
    }

    /**
     * Make CSV file for the list of PSV Operators
     *
     * @return string
     */
    protected function makeCsvForPsvOperatorList(Result $dbalResult)
    {
        $this->result->addMessage('create csv file content');

        $handle = fopen('php://temp', 'r+');

        $titleAdded = false;

        //  add rows
        while (($row = $dbalResult->fetchAssociative()) !== false) {
            if (!$titleAdded) {
                //  add title & first row
                fputcsv($handle, array_keys($row));
                $titleAdded = true;
            }

            fputcsv($handle, $row);
        }

        rewind($handle);
        $fileContents = stream_get_contents($handle);

        fclose($handle);

        return $fileContents;
    }

    /**
     * Define list of traffic areas for which should be created report(s)
     *
     * @return TrafficAreaEntity[]
     */
    protected function getTrafficAreas()
    {
        /** @var Repository\TrafficArea $repo */
        $repo = $this->getRepo('TrafficArea');

        //  remove Northern Ireland
        $items = array_filter(
            $repo->fetchAll(),
            fn(TrafficAreaEntity $item) => $item->getId() !== TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE
        );

        if (count($items) === 0) {
            throw new Exception(self::ERR_NO_TRAFFIC_AREAS);
        }

        return $items;
    }

    protected function createManifest(array $filePaths)
    {
        $manifestLines = [];

        foreach ($filePaths as $filePath) {
            $hash = hash_file('sha256', $filePath);
            $fileName = basename((string) $filePath);
            $manifestLines[] = $hash . '  ' . $fileName;
        }

        $manifestContent = implode("\n", $manifestLines);

        $manifestPath = sys_get_temp_dir() . '/dvaoplic-manifest.txt';
        file_put_contents($manifestPath, $manifestContent);

        return $manifestPath;
    }

    protected function createTarGzArchive(array $filePaths, $manifestPath)
    {
        $date = new DateTime('now');
        $archiveBaseName = 'dvaoplic-' . $date->format(static::FILE_DATETIME_FORMAT) . '.tar';
        $archiveGzBaseName = $archiveBaseName . '.gz';
        $archivePath = sys_get_temp_dir() . '/' . $archiveBaseName;
        $archiveGzPath = sys_get_temp_dir() . '/' . $archiveGzBaseName;

        $tar = new \PharData($archivePath);

        foreach ($filePaths as $filePath) {
            $tar->addFile($filePath, basename((string) $filePath));
        }

        $tar->addFile($manifestPath, basename((string) $manifestPath));
        $tar->compress(\Phar::GZ);
        unlink($archivePath);

        return $archiveGzPath;
    }

    protected function uploadToS3($filePath)
    {
        $fileName = basename((string) $filePath);
        $fileResource = fopen($filePath, 'r');

        $this->s3Client->putObject([
            'Bucket' => $this->s3Bucket,
            'Key'    => $this->path . '/' . $fileName,
            'Body'   => $fileResource,
        ]);

        fclose($fileResource);

        $this->result->addMessage('Uploaded file to S3: ' . $fileName);
    }

    protected function cleanUpFiles(array $filePaths)
    {
        foreach ($filePaths as $filePath) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}
