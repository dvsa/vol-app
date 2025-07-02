<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\PrintScheduler;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Exception\Exception;
use Dvsa\Olcs\Api\Domain\Exception\NotReadyException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\PrintScan\Printer;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Service\ConvertToPdf\ConvertToPdfInterface;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Laminas\Stdlib\Glob;

/**
 * Print Job
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PrintJob extends AbstractCommandHandler
{
    public const DEF_PRINT_COPIES_CNT = 1;

    public const TEMP_DIR = '/tmp/';
    public const TEMP_FILE_PREFIX = 'PrintJob';

    protected $repoServiceName = 'Document';

    protected $extraRepos = ['User', 'SystemParameter', 'Printer'];

    /** @var string */
    private $destination;

    /** @var string */
    private $filesPrefix;

    /** @var int */
    private $stubPrintToLicenceId;

    public function __construct(private array $config, private ContentStoreFileUploader $fileUploader, private ConvertToPdfInterface $convertToPdfService)
    {
    }

    /**
     * Handle Command
     *
     * @param \Dvsa\Olcs\Api\Domain\Command\PrintScheduler\PrintJob $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $user User */
        if (!empty($command->getUser())) {
            $user = $this->getRepo('User')->fetchById($command->getUser());
            // get the username of the person who has sent the print job
            $username = $user->getLoginId();
        } else {
            $user = null;
            $username = 'Anonymous';
        }

        // set the common file prefix
        $this->filesPrefix = self::TEMP_FILE_PREFIX . '-' . $command->getId() . '-';

        // reset the object's properties before processing
        // the queue runner reuses the same object during the execution
        $this->destination = null;
        $this->stubPrintToLicenceId = null;

        $pdfFiles = [];

        try {
            foreach ($command->getDocuments() as $docId) {
                /* @var $document Document */
                $document = $this->getRepo('Document')->fetchById($docId);

                // get destination ie the CUPS print queue name
                $destination = $this->getDestination($document, $user);

                // This allows testing without have to actually connect to multiple printers/queues
                if (isset($this->stubPrintToLicenceId)) {
                    $this->stubPrint($document, $this->stubPrintToLicenceId);
                    continue;
                }

                // download the document
                $file = $this->fileUploader->download($document->getIdentifier());
                if ($file === null) {
                    throw new Exception('Can\'t find document');
                }

                // create a temp file
                $fileName = $this->createTmpFile($file, $this->filesPrefix, basename((string) $document->getFilename()));

                // unset no longer needed vars
                unset($file, $document);

                // convert to pdf
                $pdfFiles[] = $this->convertToPdf($fileName);
            }

            if (!empty($pdfFiles)) {
                // merge all pdf files into one
                $pdfFile = $this->mergeFiles($pdfFiles);

                // print the pdf
                $this->printFile($pdfFile, basename($pdfFile), $destination, $username, $command->getCopies());
            }
        } finally {
            // if something goes wrong, still delete temp files
            $this->deleteTempFiles();
        }

        $this->result->addMessage(
            isset($this->stubPrintToLicenceId)
            ? 'Printed successfully (stub to licence ' . $this->stubPrintToLicenceId . ')'
            : 'Printed successfully'
        );

        return $this->result;
    }

    /**
     * Create a temporary file
     *
     * @param File   $file       File contents to be written to the tmp file
     * @param string $prefix     Temporary filename prefix
     * @param string $fileSuffix Temporary filename suffix
     *
     * @return string path of temporary file
     *
     * @throws Exception
     */
    protected function createTmpFile(File $file, $prefix = '', $fileSuffix = '')
    {
        $tmpFile = str_replace(' ', '_', self::TEMP_DIR . $prefix . uniqid() . '-' . $fileSuffix);

        if (file_put_contents($tmpFile, trim($file->getContent()))) {
            return $tmpFile;
        }

        throw new Exception('Can\'t create tmp file');
    }

    /**
     * Execute a system command
     *
     * @param string $command CLI command to execute
     * @param array  &$output Output from command
     * @param int    &$result Result/exit code
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function executeCommand($command, &$output, &$result)
    {
        exec($command, $output, $result);
    }

    /**
     * Wrap file exists function
     *
     * @param string $file File to test
     *
     * @return bool
     * @codeCoverageIgnore
     */
    protected function fileExists($file)
    {
        return file_exists($file);
    }

    /**
     * Delete temporary files, RTF and PDF
     *
     * @return void
     */
    protected function deleteTempFiles()
    {
        if (!empty($this->filesPrefix)) {
            // remove temporary rtf|pdf files
            $pattern = self::TEMP_DIR . $this->filesPrefix . '*.{rtf,pdf}';
            $files = Glob::glob($pattern, Glob::GLOB_BRACE);

            if (is_array($files)) {
                array_map('unlink', $files);
            }
        }
    }

    /**
     * Merge a list of files
     *
     * @param array $files List of files to merge
     *
     * @return string
     * @throws NotReadyException
     * @throws RuntimeException
     */
    protected function mergeFiles(array $files)
    {
        if (sizeof($files) === 1) {
            // there is only one file on the list - return it
            return $files[0];
        }

        $pdfOutput = self::TEMP_DIR . $this->filesPrefix . 'print.pdf';

        // send to pdf merge tool
        // 2>&1 redirect STDERR to STDOUT so that any errors are included in $output
        $command = sprintf(
            'pdfunite %s %s 2>&1',
            implode(' ', array_map('escapeshellarg', $files)),
            escapeshellarg($pdfOutput)
        );

        $output = [];
        $result = 0;

        $this->executeCommand($command, $output, $result);

        if ($result !== 0) {
            $exception = new NotReadyException('Error executing pdfunite command : ' . implode("\n", $output));
            $exception->setRetryAfter(60);
            throw $exception;
        }

        return $pdfOutput;
    }

    /**
     * Print a file
     *
     * @param string $pdfFile     PDF file to print
     * @param string $jobTitle    Job name
     * @param string $destination Destination print queue
     * @param string $username    Username of person printing
     * @param int    $copies      Count of copies
     *
     * @return void
     * @throws NotReadyException
     * @throws RuntimeException
     */
    protected function printFile($pdfFile, $jobTitle, $destination, $username, $copies)
    {
        $printServer = $this->getConfigPrintServer();
        if ($printServer === false) {
            throw new RuntimeException('print.server is not set in config');
        }

        // Check the PDF file was created
        if (!$this->fileExists($pdfFile)) {
            $exception = new NotReadyException('PDF file does not exist : ' . $pdfFile);
            $exception->setRetryAfter(60);
            throw $exception;
        }

        // This config allows us to override the username on the environments that use the CUPS PDF print driver
        // This is needed as the username has to exist on the CUPS box so that the PDF file can be created
        if ($this->getConfigUser() !== false) {
            $username = $this->getConfigUser();
        }

        // send to CUPS server
        // 2>&1 redirect STDERR to STDOUT so that any errors are included in $outputPrint
        $commandPrint = sprintf(
            'lpr %s -H %s -C %s -h -P %s -U %s -#%d -o collate=true 2>&1',
            escapeshellarg($pdfFile),
            escapeshellarg($printServer),
            escapeshellarg($jobTitle),
            escapeshellarg($destination),
            escapeshellarg($username),
            ((int)$copies ?: self::DEF_PRINT_COPIES_CNT)
        );

        $outputPrint = [];
        $resultPrint = 0;

        $this->executeCommand($commandPrint, $outputPrint, $resultPrint);
        if ($resultPrint !== 0) {
            $exception = new NotReadyException('Error executing lpr command : ' . implode("\n", $outputPrint));
            $exception->setRetryAfter(60);
            throw $exception;
        }
    }

    /**
     * Get destination
     *
     * @param Document $document Document
     * @param User     $user     User
     *
     * @return string
     * @throws Exception
     */
    protected function getDestination(Document $document, User $user = null)
    {
        // this method assumes that all documents in one message have the same destination
        if (!isset($this->destination)) {
            // get destination ie the CUPS print queue name
            if ($user !== null && $user->getTeam()) {
                $printer = $this->findPrinterForUserAndDocument($user, $document);
                $this->destination = $printer->getPrinterName();
            } else {
                // If user does NOT have a team, they must be selfserve. Therefore get Printer from system parameter
                $this->destination = $this->getSelfserveUserPrinter();
            }

            // if the destination (queue name) is TESTING-STUB-LICENCE:n then attach it to a licence
            // This allows testing without have to actually connect to multiple printers/queues
            if (str_starts_with($this->destination, 'TESTING-STUB-LICENCE:')) {
                $this->stubPrintToLicenceId = (int) substr($this->destination, strlen('TESTING-STUB-LICENCE:'));
            }
        }

        return $this->destination;
    }

    /**
     * Find the printer to be used for a user
     *
     * @param User     $user     User
     * @param Document $document Document
     *
     * @return Printer
     * @throws Exception
     */
    protected function findPrinterForUserAndDocument(User $user, Document $document)
    {
        $criteria = [
            // First check for user + sub cat
            Criteria::create()
                ->andWhere(Criteria::expr()->eq('user', $user))
                ->andWhere(Criteria::expr()->eq('subCategory', $document->getSubCategory())),
            // Then check for team + sub cat
            Criteria::create()
                ->andWhere(Criteria::expr()->isNull('user'))
                ->andWhere(Criteria::expr()->eq('subCategory', $document->getSubCategory())),
            // Then check for user default
            Criteria::create()
                ->andWhere(Criteria::expr()->eq('user', $user))
                ->andWhere(Criteria::expr()->isNull('subCategory')),
            // Then check for team default
            Criteria::create()
                ->andWhere(Criteria::expr()->isNull('user'))
                ->andWhere(Criteria::expr()->isNull('subCategory'))
        ];

        $teamPrinters = $user->getTeam()->getTeamPrinters();
        foreach ($criteria as $criterion) {
            $filteredPrinters = $teamPrinters->matching($criterion);

            if ($filteredPrinters->isEmpty() === false) {
                return $filteredPrinters->first()->getPrinter();
            }
        }

        throw new Exception('Cannot find printer for User ' . $user->getLoginId());
    }

    /**
     * Get the printer queue for selfserve users
     *
     * @return string
     */
    private function getSelfserveUserPrinter()
    {
        $printerQueue = $this->getRepo('SystemParameter')
            ->fetchValue(\Dvsa\Olcs\Api\Entity\System\SystemParameter::SELFSERVE_USER_PRINTER);

        return $printerQueue;
    }

    /**
     * Get print server address from config
     *
     * @return boolean|string
     */
    private function getConfigPrintServer()
    {
        return $this->config['print']['server'] ?? false;
    }

    /**
     * Get username from config
     *
     * @return boolean|string
     */
    private function getConfigUser()
    {
        return $this->config['print']['options']['user'] ?? false;
    }

    /**
     * Use the webservice to convert to PDF?
     *
     * @return bool
     */
    private function useWebService()
    {
        return isset($this->config['convert_to_pdf']['uri']) && !empty($this->config['convert_to_pdf']['uri']);
    }

    /**
     * Convert a document to a PDF so it can be printed
     *
     * @param string $fileName File to convert to PDF
     *
     * @return string PDF file name
     * @throws NotReadyException
     */
    private function convertToPdf($fileName)
    {
        $pdfFileName = str_replace('.rtf', '.pdf', $fileName);
        if ($this->useWebService()) {
            try {
                $this->convertToPdfService->convert($fileName, $pdfFileName);
            } catch (\Exception $e) {
                $exception = new NotReadyException('Error generating the PDF ' . $fileName . ' : ' . $e->getMessage());
                $exception->setRetryAfter(60);
                throw $exception;
            }
        } else {
            // convert to PDF using open office
            // 2>&1 redirect STDERR to STDOUT so that any errors are included in $outputPrint
            $commandPdf = sprintf(
                'soffice --headless --convert-to pdf:writer_pdf_Export --outdir /tmp %s 2>&1',
                escapeshellarg($fileName)
            );

            $outputPdf = [];
            $resultPdf = 0;

            $this->executeCommand($commandPdf, $outputPdf, $resultPdf);
            if ($resultPdf !== 0) {
                $exception = new NotReadyException('Error generating the PDF : ' . implode("\n", $outputPdf));
                $exception->setRetryAfter(60);
                throw $exception;
            }
        }

        return $pdfFileName;
    }

    /**
     * Stub printing by add a document to licence 7
     *
     * @param Document $document  Document to be printed
     * @param int      $licenceId Licence to attach to
     *
     * @return void
     * @codeCoverageIgnore
     */
    private function stubPrint(Document $document, $licenceId = 7)
    {
        $printDocument = new \Dvsa\Olcs\Api\Entity\Doc\Document($document->getIdentifier());

        $printDocument->setDescription("PRINT " . $document->getDescription());
        $printDocument->setFilename(str_replace(' ', '_', $document->getDescription()) . '.rtf');
        // hard coded simply so we can demo against *something*
        $printDocument->setLicence(
            $this->getRepo()->getReference(\Dvsa\Olcs\Api\Entity\Licence\Licence::class, $licenceId)
        );
        $printDocument->setCategory(
            $this->getRepo()->getCategoryReference(\Dvsa\Olcs\Api\Entity\System\Category::CATEGORY_LICENSING)
        );
        $printDocument->setSubCategory(
            $this->getRepo()->getSubCategoryReference(
                \Dvsa\Olcs\Api\Entity\System\Category::DOC_SUB_CATEGORY_LICENCE_VEHICLE_LIST
            )
        );
        $printDocument->setIsExternal(false);
        $printDocument->setIssuedDate(new \Datetime());

        $this->getRepo()->save($printDocument);
    }
}
