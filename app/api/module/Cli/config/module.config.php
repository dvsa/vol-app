<?php

use Doctrine\Migrations\Tools\Console\Command as MigrationCommands;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Cli\Domain\CommandHandler;
use Dvsa\Olcs\Cli\Domain\Query;
use Dvsa\Olcs\Cli\Domain\QueryHandler;
use Dvsa\Olcs\Cli;
use Dvsa\Olcs\Cli\Command\Batch as BatchCommands;
use Dvsa\Olcs\Cli\Command\Permits as PermitsCommands;
use Dvsa\Olcs\Cli\Command\Queue as QueueCommands;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;

$commonCommandDeps = [
    'CommandHandlerManager',
];

$commonBatchCommandDeps = [
    'CommandHandlerManager',
    'QueryHandlerManager',
];

return [
    'laminas-cli' => [
        'commands' => [
            'batch:ch-vs-olcs-diffs' => Dvsa\Olcs\Cli\Command\Batch\CompaniesHouseVsOlcsDiffsExportCommand::class,
            'batch:clean-up-variations' => Dvsa\Olcs\Cli\Command\Batch\CleanUpAbandonedVariationsCommand::class,
            'batch:cns' => Dvsa\Olcs\Cli\Command\Batch\ContinuationNotSoughtCommand::class,
            'batch:create-psv-licence-surrender-tasks' => Dvsa\Olcs\Cli\Command\Batch\CreatePsvLicenceSurrenderTasksCommand::class,
            'batch:data-dva-ni-export' => Dvsa\Olcs\Cli\Command\Batch\DataDvaNiExportCommand::class,
            'batch:data-gov-uk-export' => Dvsa\Olcs\Cli\Command\Batch\DataGovUkExportCommand::class,
            'batch:data-retention' => Dvsa\Olcs\Cli\Command\Batch\DataRetentionCommand::class,
            'batch:database-maintenance' => Dvsa\Olcs\Cli\Command\Batch\DatabaseMaintenanceCommand::class,
            'batch:digital-continuation-reminders' => Dvsa\Olcs\Cli\Command\Batch\DigitalContinuationRemindersCommand::class,
            'batch:duplicate-vehicle-warning' => Dvsa\Olcs\Cli\Command\Batch\DuplicateVehicleWarningCommand::class,
            'batch:duplicate-vehicle-removal' => Dvsa\Olcs\Cli\Command\Batch\DuplicateVehicleRemovalCommand::class,
            'batch:enqueue-ch-compare' => Dvsa\Olcs\Cli\Command\Batch\EnqueueCompaniesHouseCompareCommand::class,
            'batch:expire-bus-registration' => Dvsa\Olcs\Cli\Command\Batch\ExpireBusRegistrationCommand::class,
            'batch:flag-urgent-tasks' => Dvsa\Olcs\Cli\Command\Batch\FlagUrgentTasksCommand::class,
            'batch:import-users-from-csv' => Dvsa\Olcs\Cli\Command\Batch\ImportUsersFromCsvCommand::class,
            'batch:inspection-request-email' => Dvsa\Olcs\Cli\Command\Batch\InspectionRequestEmailCommand::class,
            'batch:interim-end-date-enforcement' => Dvsa\Olcs\Cli\Command\Batch\InterimEndDateEnforcementCommand::class,
            'batch:last-tm-letter' => Dvsa\Olcs\Cli\Command\Batch\LastTmLetterCommand::class,
            'batch:licence-status-rules' => Dvsa\Olcs\Cli\Command\Batch\LicenceStatusRulesCommand::class,
            'batch:process-cl' => Dvsa\Olcs\Cli\Command\Batch\ProcessCommunityLicencesCommand::class,
            'batch:process-inbox' => Dvsa\Olcs\Cli\Command\Batch\ProcessInboxDocumentsCommand::class,
            'batch:process-ntu' => Dvsa\Olcs\Cli\Command\Batch\ProcessNtuCommand::class,
            'batch:remove-read-audit' => Dvsa\Olcs\Cli\Command\Batch\RemoveReadAuditCommand::class,
            'batch:resolve-payments' => Dvsa\Olcs\Cli\Command\Batch\ResolvePaymentsCommand::class,
            'permits:cancel-unsubmitted-bilateral' => Dvsa\Olcs\Cli\Command\Permits\CancelUnsubmittedBilateralCommand::class,
            'permits:close-expired-windows' => Dvsa\Olcs\Cli\Command\Permits\CloseExpiredWindowsCommand::class,
            'permits:mark-expired-permits' => Dvsa\Olcs\Cli\Command\Permits\MarkExpiredPermitsCommand::class,
            'permits:withdraw-unpaid' => Dvsa\Olcs\Cli\Command\Permits\WithdrawUnpaidIrhpCommand::class,
            'queue:process-queue' => Dvsa\Olcs\Cli\Command\Queue\ProcessQueueCommand::class,
            'queue:process-company-profile' => Dvsa\Olcs\Cli\Command\Queue\ProcessCompanyProfileSQSQueueCommand::class,
            'queue:company-profile-dlq' => Dvsa\Olcs\Cli\Command\Queue\CompanyProfileDlqSQSQueueCommand::class,
            'queue:process-insolvency' => Dvsa\Olcs\Cli\Command\Queue\ProcessInsolvencySQSQueueCommand::class,
            'queue:process-insolvency-dlq' => Dvsa\Olcs\Cli\Command\Queue\ProcessInsolvencyDlqSQSQueueCommand::class,
            'queue:transxchange-consumer' => Dvsa\Olcs\Cli\Command\Queue\TransXChangeConsumerSQSQueueCommand::class,
            'migrations:current'    => MigrationCommands\CurrentCommand::class,
            'migrations:execute'    => MigrationCommands\ExecuteCommand::class,
            'migrations:generate'   => MigrationCommands\GenerateCommand::class,
            'migrations:latest'     => MigrationCommands\LatestCommand::class,
            'migrations:list'       => MigrationCommands\ListCommand::class,
            'migrations:migrate'    => MigrationCommands\MigrateCommand::class,
            'migrations:status'     => MigrationCommands\StatusCommand::class,
            'migrations:version'    => MigrationCommands\VersionCommand::class,
            'entity:generate'       => Dvsa\Olcs\Cli\Command\EntityGenerator\GenerateEntitiesCommand::class,
        ],
    ],
    'dependencies' => [
        'factories' => [
            // DependencyFactory setup
            DependencyFactory::class => function (ContainerInterface $container) {
                $config = $container->get('config');
                $doctrineConfig = $config['doctrine'];
                $migrationsConfig = $doctrineConfig['migrations'];

                $configurationArray = [
                    'migrations_paths' => [
                        $migrationsConfig['namespace'] => $migrationsConfig['directory'],
                    ],
                    'table_storage' => [
                        'table_name'          => $migrationsConfig['table'],
                        'version_column_name' => $migrationsConfig['column'],
                    ],
                    'all_or_nothing'          => $migrationsConfig['all_or_nothing'],
                    'check_database_platform' => $migrationsConfig['check_database_platform'],
                ];

                $configArray = new ConfigurationArray($configurationArray);

                $entityManager = $container->get('doctrine.entitymanager.orm_default');

                return DependencyFactory::fromEntityManager(
                    $configArray,
                    new \Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager($entityManager)
                );
            },
            // Factories for Doctrine Migrations commands
            MigrationCommands\CurrentCommand::class => function (ContainerInterface $container) {
                return new MigrationCommands\CurrentCommand($container->get(DependencyFactory::class));
            },
            MigrationCommands\ExecuteCommand::class => function (ContainerInterface $container) {
                return new MigrationCommands\ExecuteCommand($container->get(DependencyFactory::class));
            },
            MigrationCommands\GenerateCommand::class => function (ContainerInterface $container) {
                return new MigrationCommands\GenerateCommand($container->get(DependencyFactory::class));
            },
            MigrationCommands\LatestCommand::class => function (ContainerInterface $container) {
                return new MigrationCommands\LatestCommand($container->get(DependencyFactory::class));
            },
            MigrationCommands\ListCommand::class => function (ContainerInterface $container) {
                return new MigrationCommands\ListCommand($container->get(DependencyFactory::class));
            },
            MigrationCommands\MigrateCommand::class => function (ContainerInterface $container) {
                return new MigrationCommands\MigrateCommand($container->get(DependencyFactory::class));
            },
            MigrationCommands\StatusCommand::class => function (ContainerInterface $container) {
                return new MigrationCommands\StatusCommand($container->get(DependencyFactory::class));
            },
            MigrationCommands\VersionCommand::class => function (ContainerInterface $container) {
                return new MigrationCommands\VersionCommand($container->get(DependencyFactory::class));
            },
            
            
            
        ],
    ],
    'service_manager' => [
        'abstract_factories' => [
            ConfigAbstractFactory::class,
        ],
        'invokables' => [
            'Request' => Cli\Request\CliRequest::class,
        ],
        'factories' => [
            'MessageConsumerManager' => \Dvsa\Olcs\Cli\Service\Queue\MessageConsumerManagerFactory::class,
            'Queue' => Dvsa\Olcs\Cli\Service\Queue\QueueProcessorFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractConsumerServices::class
            => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\AbstractConsumerServicesFactory::class,
            
            // Entity Generator Services
            \Dvsa\Olcs\Cli\Service\EntityGenerator\Adapters\Doctrine3SchemaIntrospector::class => function (ContainerInterface $container) {
                return new \Dvsa\Olcs\Cli\Service\EntityGenerator\Adapters\Doctrine3SchemaIntrospector(
                    $container->get('doctrine.connection.orm_default'),
                    $container->get('config')['entity_generator'] ?? []
                );
            },
            
            \Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlerRegistry::class => function (ContainerInterface $container) {
                $registry = new \Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlerRegistry();
                
                // Register type handlers in priority order
                $registry->register(new \Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers\PrimaryKeyTypeHandler());
                $registry->register(new \Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers\VersionTypeHandler());
                $registry->register(new \Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers\BlameableTypeHandler());
                $registry->register(new \Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers\YesNoTypeHandler());
                $registry->register(new \Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers\EncryptedStringTypeHandler());
                $registry->register(new \Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers\RelationshipTypeHandler());
                $registry->register(new \Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers\DefaultTypeHandler());
                
                return $registry;
            },
            
            \Dvsa\Olcs\Cli\Service\EntityGenerator\MethodGeneratorService::class => function (ContainerInterface $container) {
                return new \Dvsa\Olcs\Cli\Service\EntityGenerator\MethodGeneratorService();
            },
            
            \Dvsa\Olcs\Cli\Service\EntityGenerator\PropertyNameResolver::class => function (ContainerInterface $container) {
                return new \Dvsa\Olcs\Cli\Service\EntityGenerator\PropertyNameResolver();
            },
            
            \Dvsa\Olcs\Cli\Service\EntityGenerator\TemplateRenderer::class => function (ContainerInterface $container) {
                return new \Dvsa\Olcs\Cli\Service\EntityGenerator\TemplateRenderer(
                    __DIR__ . '/../src/Service/EntityGenerator/Templates',
                    $container->get(\Dvsa\Olcs\Cli\Service\EntityGenerator\MethodGeneratorService::class)
                );
            },
            
            \Dvsa\Olcs\Cli\Service\EntityGenerator\EntityGenerator::class => function (ContainerInterface $container) {
                return new \Dvsa\Olcs\Cli\Service\EntityGenerator\EntityGenerator(
                    $container->get(\Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlerRegistry::class),
                    $container->get(\Dvsa\Olcs\Cli\Service\EntityGenerator\TemplateRenderer::class),
                    $container->get(\Dvsa\Olcs\Cli\Service\EntityGenerator\EntityConfigService::class),
                    $container->get(\Dvsa\Olcs\Cli\Service\EntityGenerator\InverseRelationshipProcessor::class),
                    $container->get(\Dvsa\Olcs\Cli\Service\EntityGenerator\PropertyNameResolver::class)
                );
            },
            
            \Dvsa\Olcs\Cli\Service\EntityGenerator\EntityConfigService::class => function (ContainerInterface $container) {
                $configPath = __DIR__ . '/../../../data/db/EntityConfig.php';
                return new \Dvsa\Olcs\Cli\Service\EntityGenerator\EntityConfigService($configPath);
            },
            
            \Dvsa\Olcs\Cli\Service\EntityGenerator\InverseRelationshipProcessor::class => function (ContainerInterface $container) {
                return new \Dvsa\Olcs\Cli\Service\EntityGenerator\InverseRelationshipProcessor(
                    $container->get(\Dvsa\Olcs\Cli\Service\EntityGenerator\EntityConfigService::class),
                    $container->get(\Dvsa\Olcs\Cli\Service\EntityGenerator\PropertyNameResolver::class)
                );
            },
        ],
    ],
    ConfigAbstractFactory::class => [
        BatchCommands\CleanUpAbandonedVariationsCommand::class => $commonBatchCommandDeps,
        BatchCommands\CompaniesHouseVsOlcsDiffsExportCommand::class => $commonBatchCommandDeps,
        BatchCommands\ContinuationNotSoughtCommand::class => $commonBatchCommandDeps,
        BatchCommands\CreatePsvLicenceSurrenderTasksCommand::class => $commonBatchCommandDeps,
        BatchCommands\DataDvaNiExportCommand::class => $commonBatchCommandDeps,
        BatchCommands\DataGovUkExportCommand::class => $commonBatchCommandDeps,
        BatchCommands\DataRetentionCommand::class => $commonBatchCommandDeps,
        BatchCommands\DatabaseMaintenanceCommand::class => $commonBatchCommandDeps,
        BatchCommands\DigitalContinuationRemindersCommand::class => $commonBatchCommandDeps,
        BatchCommands\DuplicateVehicleWarningCommand::class => $commonBatchCommandDeps,
        BatchCommands\DuplicateVehicleRemovalCommand::class => $commonBatchCommandDeps,
        BatchCommands\EnqueueCompaniesHouseCompareCommand::class => $commonBatchCommandDeps,
        BatchCommands\ExpireBusRegistrationCommand::class => $commonBatchCommandDeps,
        BatchCommands\FlagUrgentTasksCommand::class => $commonBatchCommandDeps,
        BatchCommands\ImportUsersFromCsvCommand::class => $commonBatchCommandDeps,
        BatchCommands\InspectionRequestEmailCommand::class => $commonBatchCommandDeps,
        BatchCommands\InterimEndDateEnforcementCommand::class => $commonBatchCommandDeps,
        BatchCommands\LastTmLetterCommand::class => $commonBatchCommandDeps,
        BatchCommands\LicenceStatusRulesCommand::class => $commonBatchCommandDeps,
        BatchCommands\ProcessCommunityLicencesCommand::class => $commonBatchCommandDeps,
        BatchCommands\ProcessInboxDocumentsCommand::class => $commonBatchCommandDeps,
        BatchCommands\ProcessNtuCommand::class => $commonBatchCommandDeps,
        BatchCommands\RemoveReadAuditCommand::class => $commonBatchCommandDeps,
        BatchCommands\ResolvePaymentsCommand::class => $commonBatchCommandDeps,
        PermitsCommands\CancelUnsubmittedBilateralCommand::class => $commonBatchCommandDeps,
        PermitsCommands\CloseExpiredWindowsCommand::class => $commonBatchCommandDeps,
        PermitsCommands\MarkExpiredPermitsCommand::class => $commonBatchCommandDeps,
        PermitsCommands\WithdrawUnpaidIrhpCommand::class => $commonBatchCommandDeps,
        QueueCommands\ProcessQueueCommand::class => [...$commonCommandDeps, ...['config', 'Queue']],
        QueueCommands\CompanyProfileDlqSQSQueueCommand::class => $commonCommandDeps,
        QueueCommands\ProcessCompanyProfileSQSQueueCommand::class => $commonCommandDeps,
        QueueCommands\ProcessInsolvencySQSQueueCommand::class => $commonCommandDeps,
        QueueCommands\ProcessInsolvencyDlqSQSQueueCommand::class => $commonCommandDeps,
        QueueCommands\TransXChangeConsumerSQSQueueCommand::class => $commonCommandDeps,
        \Dvsa\Olcs\Cli\Command\EntityGenerator\GenerateEntitiesCommand::class => $commonCommandDeps,
    ],
    'cache' => [
        'adapter' => [
            // apc_cli is not currently enabled in environments therefore change it
            'name' => 'memory',
        ]
    ],
    'message_consumer_manager' => [
        'factories' => [
            Dvsa\Olcs\Cli\Service\Queue\Consumer\CompaniesHouse\Compare::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\ContinuationChecklist::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\ContinuationChecklistReminderGenerateLetter::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Tm\Snapshot::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Cpms\ReportDownload::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Cpms\ReportDownloadFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Ebsr\RequestMap::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Ebsr\ProcessPack::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Ebsr\ProcessPackFailed::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Email\Send::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\PrintJob\PrintJob::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\DiscPrinting\PrintDiscs::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\DiscPrinting\CreateGoodsVehicleList::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\DiscPrinting\CreatePsvVehicleList::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Tm\UpdateTmNysiisName::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Licence\ProcessContinuationNotSought::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Licence\SendContinuationNotSought::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\CommunityLicence\CreateForLicence::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\RemoveDeleteDocuments::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\ContinuationSnapshot::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\ContinuationDigitalReminder::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\AllocateIrhpApplicationPermits::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\RunScoring::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\AcceptScoring::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\GeneratePermits::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\GenerateReport::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\CommunityLicence\ReportingBulkReprint::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\BulkSend\Letter::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\BulkSend\Email::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\PostScoringEmail::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\PostSubmitTasks::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\CreateTask::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\GenericFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\CpidOrganisationExport::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\CpidOrganisationExportFactory::class,
            Dvsa\Olcs\Cli\Service\Queue\Consumer\RefundInterimFees::class
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory\RefundInterimFeesFactory::class,
        ],
        'aliases' => [
            Queue::TYPE_COMPANIES_HOUSE_COMPARE
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\CompaniesHouse\Compare::class,
            Queue::TYPE_CONT_CHECKLIST
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\ContinuationChecklist::class,
            Queue::TYPE_CONT_CHECKLIST_REMINDER_GENERATE_LETTER
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\ContinuationChecklistReminderGenerateLetter::class,
            Queue::TYPE_TM_SNAPSHOT
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Tm\Snapshot::class,
            Queue::TYPE_CPMS_REPORT_DOWNLOAD
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Cpms\ReportDownload::class,
            Queue::TYPE_EBSR_REQUEST_MAP
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Ebsr\RequestMap::class,
            Queue::TYPE_EBSR_PACK
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Ebsr\ProcessPack::class,
            Queue::TYPE_EBSR_PACK_FAILED
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Ebsr\ProcessPackFailed::class,
            Queue::TYPE_EMAIL
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Email\Send::class,
            Queue::TYPE_PRINT
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\PrintJob\PrintJob::class,
            Queue::TYPE_DISC_PRINTING_PRINT
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\PrintJob\PrintJob::class,
            Queue::TYPE_DISC_PRINTING
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\DiscPrinting\PrintDiscs::class,
            Queue::TYPE_CREATE_GOODS_VEHICLE_LIST
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\DiscPrinting\CreateGoodsVehicleList::class,
            Queue::TYPE_CREATE_PSV_VEHICLE_LIST
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\DiscPrinting\CreatePsvVehicleList::class,
            Queue::TYPE_UPDATE_NYSIIS_TM_NAME
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Tm\UpdateTmNysiisName::class,
            Queue::TYPE_CNS
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Licence\ProcessContinuationNotSought::class,
            Queue::TYPE_CNS_EMAIL
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Licence\SendContinuationNotSought::class,
            Queue::TYPE_CREATE_COM_LIC
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\CommunityLicence\CreateForLicence::class,
            Queue::TYPE_REMOVE_DELETED_DOCUMENTS
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\RemoveDeleteDocuments::class,
            Queue::TYPE_CREATE_CONTINUATION_SNAPSHOT
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\ContinuationSnapshot::class,
            Queue::TYPE_CONT_DIGITAL_REMINDER
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\ContinuationDigitalReminder::class,
            Queue::TYPE_IRHP_APPLICATION_PERMITS_ALLOCATE
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\AllocateIrhpApplicationPermits::class,
            Queue::TYPE_RUN_ECMT_SCORING
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\RunScoring::class,
            Queue::TYPE_ACCEPT_ECMT_SCORING
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\AcceptScoring::class,
            Queue::TYPE_PERMIT_GENERATE
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\GeneratePermits::class,
            Queue::TYPE_PERMIT_REPORT
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\GenerateReport::class,
            Queue::TYPE_PERMIT_PRINT
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\PrintJob\PrintJob::class,
            Queue::TYPE_COMM_LIC_BULK_REPRINT
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\CommunityLicence\ReportingBulkReprint::class,
            Queue::TYPE_LETTER_BULK_UPLOAD
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\BulkSend\Letter::class,
            Queue::TYPE_EMAIL_BULK_UPLOAD
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\BulkSend\Email::class,
            Queue::TYPE_POST_SCORING_EMAIL
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\PostScoringEmail::class,
            Queue::TYPE_PERMITS_POST_SUBMIT
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits\PostSubmitTasks::class,
            Queue::TYPE_CREATE_TASK
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\CreateTask::class,
            Queue::TYPE_CPID_EXPORT_CSV
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\CpidOrganisationExport::class,
            Queue::TYPE_REFUND_INTERIM_FEES
                => Dvsa\Olcs\Cli\Service\Queue\Consumer\RefundInterimFees::class,
        ],
    ],
    'queue' => [
        //'isLongRunningProcess' => true,
        'runFor' => 60,
    ],
    'file-system' => [
        'path' => '/tmp'
    ],
    \Dvsa\Olcs\Api\Domain\CommandHandlerManagerFactory::CONFIG_KEY => [
        'factories' => [
            Cli\Domain\Command\RemoveReadAudit::class => CommandHandler\RemoveReadAudit::class,
            Cli\Domain\Command\CleanUpAbandonedVariations::class => CommandHandler\CleanUpAbandonedVariations::class,
            Cli\Domain\Command\CreateViExtractFiles::class => CommandHandler\CreateViExtractFiles::class,
            Cli\Domain\Command\SetViFlags::class => CommandHandler\SetViFlags::class,
            Cli\Domain\Command\DataGovUkExport::class => CommandHandler\DataGovUkExport::class,
            Cli\Domain\Command\DataDvaNiExport::class => CommandHandler\DataDvaNiExport::class,
            Cli\Domain\Command\CompaniesHouseVsOlcsDiffsExport::class => CommandHandler\CompaniesHouseVsOlcsDiffsExport::class,
            Cli\Domain\Command\Bus\Expire::class => CommandHandler\Bus\Expire::class,
            Cli\Domain\Command\Permits\WithdrawUnpaidIrhp::class => CommandHandler\Permits\WithdrawUnpaidIrhp::class,
            Cli\Domain\Command\LastTmLetter::class => CommandHandler\LastTmLetter::class,
            Cli\Domain\Command\Permits\CloseExpiredWindows::class => CommandHandler\Permits\CloseExpiredWindows::class,
            Cli\Domain\Command\Permits\CancelUnsubmittedBilateral::class => CommandHandler\Permits\CancelUnsubmittedBilateral::class,
            Cli\Domain\Command\Permits\MarkExpiredPermits::class => CommandHandler\Permits\MarkExpiredPermits::class,
            Cli\Domain\Command\InterimEndDateEnforcement::class => CommandHandler\InterimEndDateEnforcement::class,
            Cli\Domain\Command\EntityGenerator\GenerateEntities::class => CommandHandler\EntityGenerator\GenerateEntities::class,
        ],
    ],

    \Dvsa\Olcs\Api\Domain\QueryHandlerManagerFactory::CONFIG_KEY => [
        'factories' => [
            Query\Util\GetDbValue::class => QueryHandler\Util\GetDbValue::class
        ]
    ],

    'batch_config' => [
        'remove-read-audit' => [
            'max-age' => '1 year'
        ],
        'clean-abandoned-variations' => [
            'older-than' => '4 hours'
        ]
    ]
];
