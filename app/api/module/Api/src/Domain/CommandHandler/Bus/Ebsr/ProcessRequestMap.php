<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Service\Ebsr\S3Processor;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessRequestMap as RequestMapCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\TransExchangeAwareInterface;
use Dvsa\Olcs\Api\Domain\TransExchangeAwareTrait;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;
use Dvsa\Olcs\Api\Domain\FileProcessorAwareInterface;
use Dvsa\Olcs\Api\Domain\FileProcessorAwareTrait;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Olcs\XmlTools\Xml\TemplateBuilder;
use Dvsa\Olcs\Api\Domain\Exception\TransxchangeException;
use Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClient;
use Olcs\Logging\Log\Logger;
use Dvsa\Olcs\Api\Service\Ebsr\FileProcessor;
use Psr\Container\ContainerInterface;

final class ProcessRequestMap extends AbstractCommandHandler implements
    TransactionedInterface,
    TransExchangeAwareInterface,
    ConfigAwareInterface,
    FileProcessorAwareInterface
{
    use TransExchangeAwareTrait;
    use ConfigAwareTrait;
    use FileProcessorAwareTrait;
    use QueueAwareTrait;
    use ToggleAwareTrait;

    public const string MISSING_TMP_DIR_ERROR = 'No tmp directory specified in config';
    public const string MISSING_PACK_FILE_ERROR = 'Could not fetch EBSR pack file';
    public const string MISSING_TEMPLATE_ERROR = 'Missing template: %s';
    public const string DOC_QUEUED_MESSAGE = 'TransXchange PDF queued: %s';
    public const string SCALE_DESC = ' (%s Scale)';

    protected $repoServiceName = 'Bus';

    private s3Processor $s3Processor;

    protected array $documentDescriptions = [
        TransExchangeClient::REQUEST_MAP_TEMPLATE => "Route Track Map PDF",
        TransExchangeClient::TIMETABLE_TEMPLATE => "Timetable PDF",
        TransExchangeClient::DVSA_RECORD_TEMPLATE => "DVSA Record PDF",
    ];

    protected TemplateBuilder $templateBuilder;

    /**
     * Transxchange map request
     * @throws TransxchangeException|RuntimeException
     */
    public function handleCommand(CommandInterface|RequestMapCmd $command): Result
    {
        /**
         * @var BusRegEntity $busReg
         * @var EbsrSubmissionEntity $submission
         */
        $config = $this->getConfig();

        if (!isset($config['ebsr']['tmp_extra_path'])) {
            Logger::info('TransXchange error', ['data' => self::MISSING_TMP_DIR_ERROR]);
            throw new TransxchangeException(self::MISSING_TMP_DIR_ERROR);
        }

        $busReg = $this->getRepo()->fetchUsingId($command);
        $ebsrSubmissions = $busReg->getEbsrSubmissions();
        $submission = $ebsrSubmissions->first();

        /** @var FileProcessor $fileProcessor */
        $fileProcessor = $this->getFileProcessor();
        $fileProcessor->setSubDirPath($config['ebsr']['tmp_extra_path']);

        try {
            $xmlFilename = $fileProcessor->fetchXmlFileNameFromDocumentStore(
                $submission->getDocument()->getIdentifier()
            );

            $s3Options = ['s3Filename' => $submission->getId() . '.xml'];
            $xmlFilename = $this->s3Processor->process($xmlFilename, $s3Options);
        } catch (\Exception $e) {
            $message = $e->getMessage();

            Logger::info(
                'TransXchange file processor error',
                [
                    'data' => self::MISSING_PACK_FILE_ERROR,
                    'processor_message' => $message,
                    'exception_class' => $e::class,
                ]
            );

            throw new TransxchangeException(self::MISSING_PACK_FILE_ERROR . ' ' . $message);
        }

        //decide which template files we need
        $templates = [TransExchangeClient::DVSA_RECORD_TEMPLATE => TransExchangeClient::DVSA_RECORD_TEMPLATE];

        //we only create the dvsa record pdf for cancellations, otherwise create all three
        if (!$busReg->isCancellation()) {
            $templates[TransExchangeClient::TIMETABLE_TEMPLATE] = TransExchangeClient::TIMETABLE_TEMPLATE;
            $templates[TransExchangeClient::REQUEST_MAP_TEMPLATE] = TransExchangeClient::REQUEST_MAP_TEMPLATE;
        }

        $scale = $command->getScale();

        foreach ($templates as $templateFile) {
            try {
                $documentDesc = $this->getDocumentDescription($templateFile, $scale);
                $template = $this->createRequestMapTemplate($templateFile, $xmlFilename, $scale);
                $this->getTransExchange()->makeRequest($template);
                $this->result->addMessage(sprintf(self::DOC_QUEUED_MESSAGE, $documentDesc));
            } catch (\Exception $e) {
                Logger::info('TransXchange error', ['data' => $e->getMessage()]);
                continue;
            }
        }
        return $this->result;
    }

    /**
     * Creates a transxchange xml file to request a map
     * @throws TransxchangeException
     */
    private function createRequestMapTemplate(string $template, string $xmlFilename, string $scale): string
    {
        $config = $this->getConfig();

        if (!isset($config['ebsr']['transexchange_publisher']['templates'][$template])) {
            throw new TransxchangeException(sprintf(self::MISSING_TEMPLATE_ERROR, $template));
        }

        $templatePath = $config['ebsr']['transexchange_publisher']['templates'][$template];
        $dir = dirname($xmlFilename);

        $substitutions = [
            'DocumentPath' => $dir,
            'DocumentName' => basename($xmlFilename),
            'OutputPath' => $dir,
            'RouteScale' => $scale
        ];

        return $this->templateBuilder->buildTemplate($templatePath, $substitutions);
    }

    /**
     * Works out the document description (route maps also have a scale)
     */
    public function getDocumentDescription(string $templateFile, string $scale): string
    {
        $scaleString = '';

        if ($templateFile === TransExchangeClient::REQUEST_MAP_TEMPLATE) {
            $scaleString = sprintf(self::SCALE_DESC, ucwords(strtolower($scale)));
        }

        return $this->documentDescriptions[$templateFile] . $scaleString;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->s3Processor = $container->get(S3Processor::class);
        $this->templateBuilder = $container->get(TemplateBuilder::class);
        return parent::__invoke($container, $requestedName, $options);
    }
}
