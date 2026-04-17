<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod as BusNoticePeriodEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Service\Ebsr\FileProcessor;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\BusRegistrationInputFactory;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\ProcessedDataInputFactory;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\ShortNoticeInputFactory;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\XmlStructureInputFactory;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as EbsrSubmissionRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Domain\Repository\BusRegOtherService as BusRegOtherServiceRepo;
use Dvsa\Olcs\Api\Domain\Repository\TrafficArea as TrafficAreaRepo;
use Dvsa\Olcs\Api\Domain\Repository\LocalAuthority as LocalAuthorityRepo;
use Dvsa\Olcs\Api\Domain\Repository\BusServiceType as BusServiceTypeRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Laminas\Filter\FilterPluginManager;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\SubmissionResult as SubmissionResultFilter;
use Laminas\InputFilter\Input;
use Dvsa\Olcs\Api\Service\Ebsr\FileProcessorInterface;

class ProcessPackTestCase extends AbstractCommandHandlerTestCase
{
    public $config = [
        'ebsr' => [
            'tmp_extra_path' => 'root'
        ]
    ];

    public function setUp(): void
    {
        $this->mockRepo('Bus', BusRepo::class);
        $this->mockRepo('EbsrSubmission', EbsrSubmissionRepo::class);
        $this->mockRepo('Document', DocumentRepo::class);
        $this->mockRepo('BusRegOtherService', BusRegOtherServiceRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('TrafficArea', TrafficAreaRepo::class);
        $this->mockRepo('LocalAuthority', LocalAuthorityRepo::class);
        $this->mockRepo('BusServiceType', BusServiceTypeRepo::class);

        $config = [
            'ebsr' => [
                'tmp_extra_path' => 'root'
            ]
        ];

        $xmlStructureInput = m::mock(Input::class);
        $busRegInput = m::mock(Input::class);
        $processedDataInput = m::mock(Input::class);
        $shortNoticeInput = m::mock(Input::class);
        $filterManager = m::mock(FilterPluginManager::class);
        $fileProcessor = m::mock(FileProcessor::class);

        $submissionResultFilter = m::mock(SubmissionResultFilter::class);

        $submissionResultFilter
            ->shouldReceive('filter')
            ->andReturn(['an_array']);

        $filterManager
            ->shouldReceive('get')
            ->with(SubmissionResultFilter::class)
            ->andReturn($submissionResultFilter);

        $this->mockedSmServices = [
            XmlStructureInputFactory::class => $xmlStructureInput,
            BusRegistrationInputFactory::class => $busRegInput,
            ProcessedDataInputFactory::class => $processedDataInput,
            ShortNoticeInputFactory::class => $shortNoticeInput,
            'config' => $config,
            'FileUploader' => m::mock(ContentStoreFileUploader::class),
            'FilterManager' => $filterManager,
            FileProcessorInterface::class => $fileProcessor
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            BusNoticePeriodEntity::class => [
                1 => m::mock(BusNoticePeriodEntity::class),
                2 => m::mock(BusNoticePeriodEntity::class)
            ]
        ];

        $this->refData = [
            EbsrSubmissionEntity::VALIDATING_STATUS,
            EbsrSubmissionEntity::PROCESSING_STATUS,
            EbsrSubmissionEntity::PROCESSED_STATUS,
            EbsrSubmissionEntity::FAILED_STATUS,
            'bs_in_part',
            BusRegEntity::STATUS_NEW,
            BusRegEntity::STATUS_CANCEL,
            BusRegEntity::STATUS_VAR,
            BusRegEntity::STATUS_REGISTERED
        ];

        parent::initReferences();
    }
}
