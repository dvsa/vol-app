<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\TransXChange;

use Aws\S3\S3Client;
use Aws\Sqs\SqsClient;
use Aws\Sts\StsClient;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\UpdateTxcInboxPdf as UpdateTxcInboxPdfCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as EbsrSubmissionRepository;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\AbstractConsumer;
use Dvsa\Olcs\Queue\Service\Queue;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Exception;
use RuntimeException;
use Psr\Container\ContainerInterface;
use Olcs\Logging\Log\Logger;
use Olcs\XmlTools\Filter\MapXmlFile;
use Olcs\XmlTools\Filter\ParseXmlString;
use Olcs\XmlTools\Validator\Xsd;

class TransXChangeConsumer extends AbstractConsumer
{
    public const TYPES = [
        'RouteMap' => 'RouteMap',
        'Timetable' => 'Timetable',
        'DvsaRecord' => 'DvsaRecord',
    ];

    public const DESCRIPTIONS = [
        self::TYPES['RouteMap'] => 'Route Track Map PDF',
        self::TYPES['Timetable'] => 'Timetable PDF',
        self::TYPES['DvsaRecord'] => 'DVSA Record PDF',
    ];

    protected array $config;

    protected ParseXmlString $xmlParser;

    protected MapXmlFile $xmlFilter;

    protected Xsd $xsdValidator;

    protected S3Client $s3Client;

    protected EbsrSubmissionRepository $ebsrSubmissionRepository;

    /**
     * @throws Exception
     */
    public function handleCommand(CommandInterface $command): Result
    {
        $allMessages = [];

        $maxMessagesPerRun = $this->config['ebsr']['max_messages_per_run'] ?? 100;

        // To loop through the entire queue we need to keep fetching messages until we get an empty/incomplete response.
        // Any messages that are not for this consumer will be made visible again.
        do {
            if (count($allMessages) >= $maxMessagesPerRun) {
                break;
            }

            $batch = $this->fetchMessages(10, 60);

            if (empty($batch)) {
                break;
            }

            array_push($allMessages, ...$batch);

            if (count($batch) < 10) {
                break;
            }
        } while (true);

        if (empty($allMessages)) {
            $this->noMessages();
            return $this->result;
        }

        $commands = [];

        foreach ($allMessages as $message) {
            try {
                $this->validateMessage($message);

                array_push($commands, ...$this->processMessage($message));

                $this->deleteMessage($message);
            } catch (NotFoundException $e) {
                Logger::warn(
                    'TransXchange message found, but not matched in the database',
                    ['data' => $e->getMessage()]
                );

                // This message is not for us, so don't delete it.
                $this->setVisibilityTimeout($message, 0);

                continue;
            } catch (Exception $e) {
                Logger::err('TransXchange error', ['data' => $e->getMessage()]);

                // If something goes unexpectedly wrong, don't delete the message so it can be retried.
                $this->setVisibilityTimeout($message, 0);

                throw $e;
            }
        }

        $result = new Result();
        $result->merge($this->handleSideEffects($commands));
        return $result;
    }

    /**
     * @param array{
     *     Body: string,
     *     MessageAttributes: array{
     *         Type: array{ StringValue: string },
     *         Scale: array{ StringValue: string }|null,
     *         InputDocumentName: array{ StringValue: string }
     *     }
     * } $message the message from the queue.
     *
     * @return AbstractCommand[]
     *
     * @throws NotFoundException
     * @throws RuntimeException
     */
    protected function processMessage(array $message): array
    {
        $inputDocumentName = $this->getQueueAttribute($message, 'InputDocumentName');

        $ebsrId = explode(".", (string) $inputDocumentName, -1)[0];

        /**
         * @var $ebsrSubmission EbsrSubmission
         */
        $ebsrSubmission = $this->ebsrSubmissionRepository->fetchById($ebsrId);

        $busRegistration = $ebsrSubmission->getBusReg();

        if (!$busRegistration) {
            throw new NotFoundException('Bus registration not found');
        }

        $documentDescription = $this->getDocumentDescription($message);

        $body = $this->parseMessageBody($message);

        if (isset($body['error'])) {
            return [$this->createFailureTaskCmd($busRegistration, $documentDescription)];
        }

        $uploadDocCmd = $this->generateDocumentCmd(
            $body['files'],
            $busRegistration,
            $documentDescription
        );

        $commands = [];

        foreach ($uploadDocCmd as $cmd) {
            $uploadedDoc = $this->handleSideEffect($cmd);
            $documentId = $uploadedDoc->getId('document');

            $type = $this->getQueueAttribute($message, 'Type');

            if (self::TYPES['Timetable'] !== $type) {
                $commands[] = $this->createUpdateTxcInboxPdfCmd(
                    $busRegistration->getId(),
                    $documentId,
                    $type
                );
            }
        }

        return $commands;
    }

    /**
     * @param array{
     *     Body: string,
     *     MessageAttributes: array{
     *         Type: array{ StringValue: string },
     *         Scale: array{ StringValue: string }|null,
     *         InputDocumentName: array{ StringValue: string }
     *     }
     * } $message the message from the queue.
     *
     * @return array{ files: string[] } the S3 object keys extracted from the message.
     *
     * @throws RuntimeException
     */
    protected function parseMessageBody(array $message): array
    {
        $dom = $this->xmlParser->filter($message['Body']);

        if (!$this->xsdValidator->isValid($dom)) {
            Logger::err(
                'TransXChange error',
                [
                    'data' => sprintf("Invalid response XML: %s", implode(', ', $this->xsdValidator->getMessages()))
                ]
            );

            throw new RuntimeException('Invalid response XML');
        }

        return $this->xmlFilter->filter($dom);
    }

    /**
     * @param array{
     *     Body: string,
     *     MessageAttributes: array{
     *         Type: array{ StringValue: string },
     *         Scale: array{ StringValue: string }|null,
     *         InputDocumentName: array{ StringValue: string }
     *     }
     * } $message the message from the queue.
     */
    protected function getDocumentDescription(array $message): string
    {
        $type = $this->getQueueAttribute($message, 'Type');

        $description = self::DESCRIPTIONS[$type];

        if ($type === self::TYPES['RouteMap']) {
            // Only the RouteMap type has a scale attribute.
            $scale = $this->getQueueAttribute($message, 'Scale') ?? "unknown";

            $description .= sprintf(' (%s Scale)', ucwords(strtolower($scale)));
        }

        return $description;
    }

    /**
     * @param array{
     *     Body: string,
     *     MessageAttributes: array{
     *         Type: array{ StringValue: string },
     *         Scale: array{ StringValue: string }|null,
     *         InputDocumentName: array{ StringValue: string }
     *     }
     * } $message the message from the queue.
     *
     * @throws RuntimeException
     */
    protected function validateMessage(array $message): void
    {
        $type = $this->getQueueAttribute($message, 'Type');

        if (!$type || !array_key_exists($type, self::TYPES)) {
            throw new RuntimeException('Missing or unknown message type.');
        }

        $inputDocumentName = $this->getQueueAttribute($message, 'InputDocumentName');

        if (!$inputDocumentName) {
            throw new RuntimeException('Missing or malformed input document name.');
        }
    }

    protected function createFailureTaskCmd(BusRegEntity $busRegistration, string $documentDescription): CreateTaskCmd
    {
        $description = sprintf('%s: PDF failed to generate: %s', $busRegistration->getRegNo(), $documentDescription);

        $data = [
            'category' => TaskEntity::CATEGORY_BUS,
            'subCategory' => TaskEntity::SUBCATEGORY_EBSR,
            'description' => $description,
            'actionDate' => date('Y-m-d'),
            'busReg' => $busRegistration->getId(),
            'licence' => $busRegistration->getLicence()->getId(),
        ];

        return CreateTaskCmd::create($data);
    }

    protected function createUpdateTxcInboxPdfCmd($busRegId, $documentId, $type): UpdateTxcInboxPdfCmd
    {
        $pdfType = $type === self::TYPES['RouteMap'] ? 'Route' : 'Pdf';

        $data = [
            'id' => $busRegId,
            'document' => $documentId,
            'pdfType' => $pdfType
        ];

        return UpdateTxcInboxPdfCmd::create($data);
    }

    protected function getFromS3(string $key): string
    {
        $result = $this->s3Client->getObject([
            'Bucket' => $this->config['ebsr']['output_s3_bucket'],
            'Key' => $key
        ]);

        return $result->get('Body');
    }

    protected function generateDocumentCmd(array $documents, BusRegEntity $busRegistration, string $description): array
    {
        $commands = [];

        foreach ($documents as $document) {
            $data = [
                'content' => base64_encode($this->getFromS3($document)),
                'busReg' => $busRegistration->getId(),
                'licence' => $busRegistration->getLicence()->getId(),
                'category' => CategoryEntity::CATEGORY_BUS_REGISTRATION,
                'subCategory' => CategoryEntity::BUS_SUB_CATEGORY_TRANSXCHANGE_PDF,
                'filename' => basename((string) $document),
                'description' => $description,
                'user' => $busRegistration->getCreatedBy()->getId(),
            ];

            $commands[] = UploadCmd::create($data);
        }

        return $commands;
    }

    /**
     * @param array{
     *      Body: string,
     *      MessageAttributes: array{
     *          Type: array{ StringValue: string },
     *          Scale: array{ StringValue: string }|null,
     *          InputDocumentName: array{ StringValue: string }
     *      }
     *  } $message the message from the queue.
     */
    protected function getQueueAttribute(array $message, string $attribute): ?string
    {
        return $message['MessageAttributes'][$attribute]['StringValue'] ?? null;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): self
    {
        parent::__invoke($container, $requestedName, $options);

        $filterManager = $container->get('FilterManager');
        $this->xmlParser = $filterManager->get(ParseXmlString::class);
        $this->xmlFilter = $filterManager->get(MapXmlFile::class);
        $this->xmlFilter->setMapping($container->get('TransExchangePublisherXmlMapping'));
        $this->xsdValidator = $container->get('ValidatorManager')->get(Xsd::class);
        $this->xsdValidator->setXsd(
            'http://naptan.dft.gov.uk/transxchange/publisher/schema/3.1.2/TransXChangePublisherService.xsd'
        );

        $this->config = $config = $container->get('config');

        $stsClient = new StsClient([
            'region' => $config['awsOptions']['region'],
            'version' => '2011-06-15'
        ] + ($config['awsOptions']['sts'] ?? []) + ($config['awsOptions']['global'] ?? []));

        $ARN = $config['ebsr']['txc_consumer_role_arn'];

        $result = $stsClient->AssumeRole([
            'RoleArn' => $ARN,
            'RoleSessionName' => 'TransXChangeConsumer'
        ]);

        $s3ClientConfiguration = [
            'region' => $config['awsOptions']['region'],
            'version' => '2006-03-01',
            'credentials' => [
                'key'    => $result['Credentials']['AccessKeyId'],
                'secret' => $result['Credentials']['SecretAccessKey'],
                'token'  => $result['Credentials']['SessionToken']
            ],
            'http'    => [
                'proxy' => $config['ebsr']['transexchange_publisher']['consumer_proxy'],
                'timeout' => 30,
            ]
        ] + ($config['awsOptions']['s3'] ?? []) + ($config['awsOptions']['global'] ?? []);

        $sqsClientConfiguration = [
            'region' => $config['awsOptions']['region'],
            'version' => '2012-11-05',
            'credentials' => [
                'key'    => $result['Credentials']['AccessKeyId'],
                'secret' => $result['Credentials']['SecretAccessKey'],
                'token'  => $result['Credentials']['SessionToken']
            ],
            'http'    => [
                'proxy' => $config['ebsr']['transexchange_publisher']['consumer_proxy'],
                'timeout' => 30,
            ]
        ] + ($config['awsOptions']['sqs'] ?? []) + ($config['awsOptions']['global'] ?? []);

        $this->s3Client = new S3Client($s3ClientConfiguration);
        $sqsClient = new SqsClient($sqsClientConfiguration);

        $this->setQueueService(new Queue($sqsClient));

        $repositoryServiceManager = $container->get('RepositoryServiceManager');

        $this->ebsrSubmissionRepository = $repositoryServiceManager->get('EbsrSubmission');

        return $this;
    }
}
