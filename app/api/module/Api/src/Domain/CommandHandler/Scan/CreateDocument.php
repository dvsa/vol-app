<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Scan;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\Api\Entity\PrintScan\Scan;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Dvsa\Olcs\Transfer\Command\Scan\CreateDocument as Cmd;

/**
 * CreateDocument
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CreateDocument extends AbstractCommandHandler implements TransactionedInterface, UploaderAwareInterface
{
    use UploaderAwareTrait;

    public const string INVALID_MIME = 'SCAN_INVALID_MIME';
    public const string SCAN_NOT_FOUND = 'SCAN_NOT_FOUND';

    protected $repoServiceName = 'Scan';

    /**
     * Allowed mime types;
     */
    private $validMimeTypes = ['application/pdf'];

    /**
     * @param Cmd $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $content = $this->validateFile($command);

        $scan = $this->findScanById($command->getScanId());

        $this->result->merge($this->generateDocument($scan, $command, $content));

        if (!$scan->isBackScan()) {
            $this->result->merge(
                $this->handleSideEffect($this->getCreateTaskCommand($scan))
            );
        }

        $this->result->addId('scan', $scan->getId());
        $this->result->addMessage("Scan ID {$scan->getId()} document created");

        // delete the scan record
        $this->getRepo()->delete($scan);

        return $this->result;
    }

    protected function validateFile(Cmd $command)
    {
        $content = base64_decode((string) $command->getContent());

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($content);

        if (!$this->isValidMimeType($mime)) {
            throw new ValidationException([self::INVALID_MIME => $mime]);
        }

        return $content;
    }

    /**
     * @param $scanId
     * @return Scan
     * @throws ValidationException
     */
    protected function findScanById($scanId)
    {
        try {
            return $this->getRepo()->fetchById($scanId);
        } catch (NotFoundException) {
            throw new ValidationException([self::SCAN_NOT_FOUND => self::SCAN_NOT_FOUND]);
        }
    }

    protected function isValidMimeType($mime)
    {
        return in_array($mime, $this->validMimeTypes);
    }

    protected function generateDocument(Scan $scan, Cmd $command, $content)
    {
        $isBackScan = $scan->isBackScan();

        $descriptionPostfix = '';
        if ($isBackScan) {
            $descriptionPostfix = ' (Back scan)';
        }

        $data = [
            'content'       => base64_encode((string) $content),
            'filename'      => $command->getFilename(),
            'description'   => $scan->getDescription() . $descriptionPostfix,
            'isExternal'    => false,
            'isScan'        => true
        ];

        if ($isBackScan) {
            $data['issuedDate'] = $scan->getDateReceived(true)->format('Y-m-d');
        }

        if ($scan->getLicence()) {
            $data['licence'] = $scan->getLicence()->getId();
        }
        if ($scan->getBusReg()) {
            $data['busReg'] = $scan->getBusReg()->getId();
        }
        if ($scan->getCase()) {
            $data['case'] = $scan->getCase()->getId();
        }
        if ($scan->getTransportManager()) {
            $data['transportManager'] = $scan->getTransportManager()->getId();
        }
        if ($scan->getCategory()) {
            $data['category'] = $scan->getCategory()->getId();
        }
        if ($scan->getSubCategory()) {
            $data['subCategory'] = $scan->getSubCategory()->getId();
        }
        if ($scan->getIrfoOrganisation()) {
            $data['irfoOrganisation'] = $scan->getIrfoOrganisation()->getId();
        }
        if ($scan->getIrhpApplication()) {
            $data['irhpApplication'] = $scan->getIrhpApplication()->getId();
        }

        return $this->handleSideEffect(Upload::create($data));
    }

    /**
     * Get the CreateTask command
     *
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Task\CreateTask
     */
    protected function getCreateTaskCommand(
        \Dvsa\Olcs\Api\Entity\PrintScan\Scan $scan
    ) {
        $params = [
            'category'       => $scan->getCategory()->getId(),
            'subCategory'    => $scan->getSubCategory()->getId(),
            'description'    => $scan->getDescription(),
        ];

        if ($scan->getLicence()) {
            $params['licence'] = $scan->getLicence()->getId();
        }
        if ($scan->getBusReg()) {
            $params['busReg'] = $scan->getBusReg()->getId();
        }
        if ($scan->getCase()) {
            $params['case'] = $scan->getCase()->getId();
        }
        if ($scan->getTransportManager()) {
            $params['transportManager'] = $scan->getTransportManager()->getId();
        }
        if ($scan->getIrfoOrganisation()) {
            $params['irfoOrganisation'] = $scan->getIrfoOrganisation()->getId();
        }
        if ($scan->getIrhpApplication()) {
            $params['irhpApplication'] = $scan->getIrhpApplication()->getId();
        }

        return \Dvsa\Olcs\Api\Domain\Command\Task\CreateTask::create($params);
    }
}
