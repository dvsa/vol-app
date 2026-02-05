<?php

/**
 * Copy document
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Document\CreateDocumentSpecific as CreateDocumentSpecificCmd;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Transfer\Command\Document\CopyDocument as CopyDocumentCmd;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;

/**
 * Copy document
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CopyDocument extends AbstractCommandHandler implements TransactionedInterface, UploaderAwareInterface
{
    use UploaderAwareTrait;

    public const string APP = 'application';
    public const string LIC = 'licence';
    public const string BUSREG = 'busReg';
    public const string CASES = 'case';
    public const string IRFO = 'irfoOrganisation';
    public const string IRHP_APP = 'irhpApplication';
    public const string TM = 'transportManager';
    public const string PUBLICATION = 'publication';

    protected $repoServiceName = 'Document';

    protected $extraRepos = [
        'Application',
        'Licence',
        'BusRegSearchView',
        'Cases',
        'Organisation',
        'TransportManager',
        'Publication',
        'IrhpApplication',
    ];

    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var CopyDocumentCmd $command
         * @var DocumentEntity $document
         */
        $entity = $this->validateTargetEntity($command->getTargetId(), $command->getType());

        $result = new Result();
        foreach ($command->getIds() as $id) {
            $document = $this->getRepo()->fetchById($id);
            $params = [
                'description' => $document->getDescription(),
                'category' => $document->getCategory()->getId(),
                'subCategory' => $document->getSubCategory()->getId(),
                'issuedDate' => $document->getIssuedDate(),
                'isScan' => $document->getIsScan(),
                'isExternal' => $document->getIsExternal(),
            ];

            $trafficArea = $document->getTrafficArea();

            if ($trafficArea instanceof TrafficAreaEntity) {
                $params['trafficArea'] = $trafficArea->getId();
            }

            switch ($command->getType()) {
                case self::APP:
                    $params['application'] = $command->getTargetId();
                    $params['licence'] = $entity->getLicence()->getId();
                    break;
                case self::LIC:
                    $params['licence'] = $entity->getId();
                    break;
                case self::BUSREG:
                    $params['busReg'] = $entity->getId();
                    $params['licence'] = $entity->getLicId();
                    break;
                case self::CASES:
                    $params['case'] = $command->getTargetId();
                    if ($entity->getLicence()) {
                        $params['licence'] = $entity->getLicence()->getId();
                    }
                    if ($entity->getApplication()) {
                        $params['application'] = $entity->getApplication()->getId();
                    }
                    if ($entity->getTransportManager()) {
                        $params['transportManager'] = $entity->getTransportManager()->getId();
                    }
                    break;
                case self::IRFO:
                    $params['irfoOrganisation'] = $command->getTargetId();
                    break;
                case self::IRHP_APP:
                    $params['irhpApplication'] = $command->getTargetId();
                    $params['licence'] = $entity->getLicence()->getId();
                    break;
                case self::TM:
                    $params['transportManager'] = $command->getTargetId();
                    break;
                case self::PUBLICATION:
            }

            $sourceDocument = $this->getUploader()->download($document->getIdentifier());
            if ($sourceDocument === null) {
                throw new ValidationException(['targetId' => 'Can\'t download the source document']);
            }
            $uploadData = array_merge(
                $params,
                [
                    'content' => [
                        'tmp_name' => $sourceDocument->getResource(),
                        'type'     => $sourceDocument->getMimeType()
                    ],
                    'filename'         => basename((string) $document->getIdentifier()),
                    'shouldUploadOnly' => true
                ]
            );
            $res = $this->handleSideEffect(UploadCmd::create($uploadData));
            $params = array_merge(
                $params,
                [
                    'identifier' => $res->getId('identifier'),
                    'filename'   => $res->getId('identifier')
                ]
            );
            $res = $this->handleSideEffect(CreateDocumentSpecificCmd::create($params));

            $result->addId('document' . $res->getId('document'), $res->getId('document'));
        }
        $result->addMessage('Document(s) copied');
        return $result;
    }

    protected function validateTargetEntity($entityId, $type)
    {
        $entity = null;
        $labels = [
            self::APP => 'Application ID',
            self::LIC => 'Licence No',
            self::BUSREG => 'Bus registration No',
            self::CASES => 'Case ID',
            self::IRFO => 'IRFO ID',
            self::IRHP_APP => 'IRHP application id',
            self::TM => 'Transport manager ID',
            self::PUBLICATION => 'Publication ID'
        ];
        try {
            $entity = match ($type) {
                self::APP => $this->getRepo('Application')->fetchWithLicence($entityId),
                self::LIC => $this->getRepo('Licence')->fetchByLicNo($entityId),
                self::BUSREG => $this->getRepo('BusRegSearchView')->fetchByRegNo($entityId),
                self::CASES => $this->getRepo('Cases')->fetchExtended($entityId),
                self::IRFO => $this->getRepo('Organisation')->fetchById($entityId),
                self::IRHP_APP => $this->getRepo('IrhpApplication')->fetchById($entityId),
                self::TM => $this->getRepo('TransportManager')->fetchById($entityId),
                self::PUBLICATION => $this->getRepo('Publication')->fetchById($entityId),
                default => throw new ValidationException(['type' => 'Unknown entity']),
            };
        } catch (NotFoundException) {
            throw new ValidationException(['targetId' => $labels[$type] . ' is invalid']);
        }
        return $entity;
    }
}
