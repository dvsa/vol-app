<?php

use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Repository\RepositoryFactory;
use Dvsa\Olcs\Api\Domain\QueryPartial;
use Dvsa\Olcs\Api\Domain\Util;
use Dvsa\Olcs\Api\Entity\Generic\Question;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Service as ApiSrv;
use Dvsa\Olcs\Api\Service\Cpms\ApiServiceFactory;
use Dvsa\Olcs\Api\Service\DvlaSearch\DvlaSearchService;
use Dvsa\Olcs\Api\Service\DvlaSearch\DvlaSearchServiceFactory;
use Dvsa\Olcs\Api\Service\Publication\PublicationGenerator;
use Dvsa\Olcs\Api\Service\Qa\Strategy as QaStrategy;
use Dvsa\Olcs\Api\Service\Translator;

return [
    'router' => [
        'routes' => include(__DIR__ . '/../../../vendor/olcs/olcs-transfer/config/backend-routes.config.php')
    ],
    'service_manager' => [
        'alias' => [
            'PublicationContextPlugin' => \Dvsa\Olcs\Api\Service\Publication\Context\PluginManager::class,
            'PublicationProcessPlugin' => \Dvsa\Olcs\Api\Service\Publication\Process\PluginManager::class,
        ],
        'invokables' => [
            'DateService' => \Dvsa\Olcs\Api\Service\Date::class,
            'RestrictionService' => \Dvsa\Olcs\Api\Service\Lva\RestrictionService::class,
            'SectionConfig' =>  \Dvsa\Olcs\Api\Service\Lva\SectionConfig::class,
            'AddressFormatter' => \Dvsa\Olcs\Api\Service\Helper\FormatAddress::class,
            'VariationPublishValidationService' =>
                \Dvsa\Olcs\Api\Service\Lva\Variation\PublishValidationService::class,
            'DoctrineLogger' => Util\DoctrineExtension\Logger::class,
            'CommonCurrentDateTimeFactory' =>
                ApiSrv\Common\CurrentDateTimeFactory::class,
            'CqrsCommandCreator' => ApiSrv\Cqrs\CommandCreator::class,
            'QaContextFactory' => ApiSrv\Qa\QaContextFactory::class,
            'QaCommonDateTimeFactory' =>
                ApiSrv\Qa\Common\DateTimeFactory::class,
            'QaCommonDateIntervalFactory' =>
                ApiSrv\Qa\Common\DateIntervalFactory::class,
            'QaCommonArrayCollectionFactory' =>
                ApiSrv\Qa\Common\ArrayCollectionFactory::class,
            'QaAnswerFactory' => ApiSrv\Qa\AnswerSaver\AnswerFactory::class,
            'QaApplicationStepFactory' => ApiSrv\Qa\Structure\ApplicationStepFactory::class,
            'QaCheckboxElementFactory' => ApiSrv\Qa\Structure\Element\Checkbox\CheckboxFactory::class,
            'QaFilteredTranslateableTextFactory' => ApiSrv\Qa\Structure\FilteredTranslateableTextFactory::class,
            'QaQuestionTextFactory' => ApiSrv\Qa\Structure\QuestionText\QuestionTextFactory::class,
            'QaSelfservePageFactory' => ApiSrv\Qa\Structure\SelfservePageFactory::class,
            'QaFormFragmentFactory' => ApiSrv\Qa\Structure\FormFragmentFactory::class,
            'QaTextElementFactory' => ApiSrv\Qa\Structure\Element\Text\TextFactory::class,
            'QaRadioElementFactory' => ApiSrv\Qa\Structure\Element\Radio\RadioFactory::class,
            'QaDateElementFactory' => ApiSrv\Qa\Structure\Element\Date\DateFactory::class,
            'QaTranslateableTextFactory' => ApiSrv\Qa\Structure\TranslateableTextFactory::class,
            'QaTranslateableTextParameterFactory' => ApiSrv\Qa\Structure\TranslateableTextParameterFactory::class,
            'QaValidatorFactory' => ApiSrv\Qa\Structure\ValidatorFactory::class,
            'QaValidatorListFactory' => ApiSrv\Qa\Structure\ValidatorListFactory::class,
            'QaElementGeneratorContextFactory' => ApiSrv\Qa\Structure\Element\ElementGeneratorContextFactory::class,
            'QaNamedAnswerFetcher' => ApiSrv\Qa\Structure\Element\NamedAnswerFetcher::class,

            'QaEcmtNoOfPermitsElementFactory' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\NoOfPermitsFactory::class,
            'QaEcmtEmissionsCategoryFactory' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\EmissionsCategoryFactory::class,
            'QaEcmtRestrictedCountriesElementFactory' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\RestrictedCountriesFactory::class,
            'QaEcmtRestrictedCountryFactory' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\RestrictedCountryFactory::class,
            'QaEcmtIntJourneysElementFactory' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\IntJourneysFactory::class,
            'QaEcmtAnnualTripsAbroadElementFactory' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\AnnualTripsAbroadFactory::class,
            'QaCommonDateWithThresholdElementFactory' =>
                ApiSrv\Qa\Structure\Element\Custom\Common\DateWithThresholdFactory::class,
            'QaSupplementedApplicationStepFactory' =>
                ApiSrv\Qa\Facade\SupplementedApplicationSteps\SupplementedApplicationStepFactory::class,
            'QaOptionListFactory' => ApiSrv\Qa\Structure\Element\Options\OptionListFactory::class,
            'QaOptionFactory' => ApiSrv\Qa\Structure\Element\Options\OptionFactory::class,

            'QaBilateralCabotageOnlyElementFactory' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\CabotageOnlyFactory::class,
            'QaBilateralCabotageOnlyAnswerSummaryProvider' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\CabotageOnlyAnswerSummaryProvider::class,
            'QaBilateralStandardAndCabotageElementFactory' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\StandardAndCabotageFactory::class,
            'QaBilateralStandardAndCabotageAnswerSummaryProvider' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\StandardAndCabotageAnswerSummaryProvider::class,
            'QaBilateralThirdCountryElementFactory' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\ThirdCountryFactory::class,
            'QaBilateralEmissionsStandardsElementFactory' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\EmissionsStandardsFactory::class,
            'QaBilateralNoOfPermitsElementFactory' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsFactory::class,
            'QaBilateralNoOfPermitsMoroccoElementFactory' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsMoroccoFactory::class,
            'QaBilateralNoOfPermitsTextFactory' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsTextFactory::class,
            'QaBilateralNoOfPermitsAnswerSummaryProvider' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsAnswerSummaryProvider::class,
            'QaBilateralNoOfPermitsMoroccoAnswerSummaryProvider' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsMoroccoAnswerSummaryProvider::class,
            'QaBilateralPermitUsageAnswerSummaryProvider' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\PermitUsageAnswerSummaryProvider::class,
            'QaCertRoadworthinessMotExpiryDateElementFactory' =>
                ApiSrv\Qa\Structure\Element\Custom\CertRoadworthiness\MotExpiryDateFactory::class,

            'PermitsAnswersSummaryFactory' => ApiSrv\Permits\AnswersSummary\AnswersSummaryFactory::class,
            'PermitsAnswersSummaryRowFactory' => ApiSrv\Permits\AnswersSummary\AnswersSummaryRowFactory::class,
            'QaGenericAnswerSummaryProvider' =>
                ApiSrv\Qa\Structure\Element\GenericAnswerSummaryProvider::class,
            'QaCheckboxAnswerSummaryProvider' =>
                ApiSrv\Qa\Structure\Element\Checkbox\CheckboxAnswerSummaryProvider::class,
            'QaDateAnswerSummaryProvider' =>
                ApiSrv\Qa\Structure\Element\Date\DateAnswerSummaryProvider::class,
            'QaEcmtNoOfPermitsAnswerSummaryProvider' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\NoOfPermitsAnswerSummaryProvider::class,
            'QaEcmtRestrictedCountriesAnswerSummaryProvider' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\RestrictedCountriesAnswerSummaryProvider::class,

            'PermitsScoringSuccessfulCandidatePermitsLogger' =>
                ApiSrv\Permits\Scoring\SuccessfulCandidatePermitsLogger::class,
            'PermitsScoringIrhpCandidatePermitFactory' =>
                ApiSrv\Permits\Scoring\IrhpCandidatePermitFactory::class,
            'PermitsApplyRangesForCpProviderFactory' =>
                ApiSrv\Permits\ApplyRanges\ForCpProviderFactory::class,
            'PermitsCandidatePermitsApggCandidatePermitFactory' =>
                ApiSrv\Permits\CandidatePermits\ApggCandidatePermitFactory::class,

            'PermitsCheckableCreateTaskCommandFactory' => ApiSrv\Permits\Checkable\CreateTaskCommandFactory::class,

            'PermitsMultilateralFeeBreakdownGenerator' =>
                ApiSrv\Permits\FeeBreakdown\MultilateralFeeBreakdownGenerator::class,

            'PermitsAllocateBilateralCriteriaFactory' => ApiSrv\Permits\Allocate\BilateralCriteriaFactory::class,
            'PermitsAllocateEmissionsStandardCriteriaFactory' =>
                ApiSrv\Permits\Allocate\EmissionsStandardCriteriaFactory::class,

            'PermitsBilateralInternalBilateralRequiredGenerator'
                => ApiSrv\Permits\Bilateral\Internal\BilateralRequiredGenerator::class,
            'PermitsBilateralInternalIrhpPermitApplicationFactory'
                => ApiSrv\Permits\Bilateral\Internal\IrhpPermitApplicationFactory::class,
            'PermitsBilateralInternalPermitUsageSelectionGenerator'
                => ApiSrv\Permits\Bilateral\Internal\PermitUsageSelectionGenerator::class,

            'PermitsBilateralMetadataMoroccoFieldsGenerator'
                => ApiSrv\Permits\Bilateral\Metadata\MoroccoFieldsGenerator::class,
            'PermitsBilateralMetadataCurrentFieldValuesGenerator'
                => ApiSrv\Permits\Bilateral\Metadata\CurrentFieldValuesGenerator::class,
        ],
        'abstract_factories' => [
            \Laminas\Cache\Service\StorageCacheAbstractServiceFactory::class,
        ],
        'factories' => [
            \Dvsa\Olcs\Api\Service\EditorJs\ConverterService::class => \Dvsa\Olcs\Api\Service\EditorJs\ConverterServiceFactory::class,
            \Dvsa\Olcs\Api\Domain\Logger\EntityAccessLogger::class => \Dvsa\Olcs\Api\Domain\Logger\EntityAccessLoggerFactory::class,
            'ConvertToPdf' => \Dvsa\Olcs\Api\Service\ConvertToPdf\ConvertToPdfFactory::class,
            'FileUploader' => \Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader::class,
            'Document' => \Dvsa\Olcs\Api\Service\Document\DocumentFactory::class,
            'DocumentGenerator' => \Dvsa\Olcs\Api\Service\Document\DocumentGenerator::class,
            'DocumentNamingService' => \Dvsa\Olcs\Api\Service\Document\NamingService::class,
            \Dvsa\Olcs\Api\Service\Letter\VolGrabReplacementService::class => \Dvsa\Olcs\Api\Service\Letter\VolGrabReplacementServiceFactory::class,
            'UpdateOperatingCentreHelper' => \Dvsa\Olcs\Api\Domain\Service\UpdateOperatingCentreHelper::class,
            'OperatingCentreHelper' => \Dvsa\Olcs\Api\Domain\Service\OperatingCentreHelper::class,
            'VariationOperatingCentreHelper' => \Dvsa\Olcs\Api\Domain\Service\VariationOperatingCentreHelper::class,
            'SectionAccessService' => \Dvsa\Olcs\Api\Service\Lva\SectionAccessService::class,
            'ApplicationGrantValidationService' => \Dvsa\Olcs\Api\Service\Lva\Application\GrantValidationService::class,
            'ApplicationPublishValidationService' => \Dvsa\Olcs\Api\Service\Lva\Application\PublishValidationService::class,
            'ContentStore' => \Dvsa\Olcs\DocumentShare\Service\ClientFactory::class,
            'PayloadValidationListener' => \Dvsa\Olcs\Api\Mvc\PayloadValidationListenerFactory::class,
            'CommandHandlerManager' => \Dvsa\Olcs\Api\Domain\CommandHandlerManagerFactory::class,
            'QueryHandlerManager' => \Dvsa\Olcs\Api\Domain\QueryHandlerManagerFactory::class,
            'ValidationHandlerManager' => \Dvsa\Olcs\Api\Domain\ValidationHandlerManagerFactory::class,
            'DomainValidatorManager' => \Dvsa\Olcs\Api\Domain\ValidatorManagerFactory::class,
            'QueryPartialServiceManager' => \Dvsa\Olcs\Api\Domain\QueryPartialServiceManagerFactory::class,
            'RepositoryServiceManager' => \Dvsa\Olcs\Api\Domain\RepositoryServiceManagerFactory::class,
            'FormControlServiceManager' => \Dvsa\Olcs\Api\Domain\FormControlServiceManagerFactory::class,
            'DbQueryServiceManager' => \Dvsa\Olcs\Api\Domain\DbQueryServiceManagerFactory::class,
            'QueryBuilder' => \Dvsa\Olcs\Api\Domain\QueryBuilderFactory::class,
            Util\SlaCalculatorInterface::class => Util\SlaCalculatorFactory::class,
            Util\TimeProcessorBuilderInterface::class => Util\TimeProcessorBuilderFactory::class,
            'TransactionManager' => \Dvsa\Olcs\Api\Domain\Repository\TransactionManagerFactory::class,
            'CpmsHelperService' => \Dvsa\Olcs\Api\Service\CpmsV2HelperService::class,
            ApiServiceFactory::class => ApiServiceFactory::class,
            'FeesHelperService' => \Dvsa\Olcs\Api\Service\FeesHelperService::class,
            'FinancialStandingHelperService' => \Dvsa\Olcs\Api\Service\FinancialStandingHelperService::class,
            DvlaSearchService::class => DvlaSearchServiceFactory::class,

            PublicationGenerator::class =>
                \Dvsa\Olcs\Api\Service\Publication\PublicationGeneratorFactory::class,

            \Dvsa\Olcs\Api\Service\Publication\Context\PluginManager::class =>
                \Dvsa\Olcs\Api\Service\Publication\Context\PluginManagerFactory::class,

            \Dvsa\Olcs\Api\Service\Publication\Process\PluginManager::class =>
                \Dvsa\Olcs\Api\Service\Publication\Process\PluginManagerFactory::class,

            \Dvsa\Olcs\Api\Service\Submission\SubmissionGenerator::class =>
                \Dvsa\Olcs\Api\Service\Submission\SubmissionGeneratorFactory::class,

            \Dvsa\Olcs\Api\Service\Submission\Sections\SectionGeneratorPluginManager::class =>
                \Dvsa\Olcs\Api\Service\Submission\Sections\SectionGeneratorPluginManagerFactory::class,

            // Letter Section Renderer Plugin System
            \Dvsa\Olcs\Api\Service\Letter\SectionRenderer\SectionRendererPluginManager::class =>
                \Dvsa\Olcs\Api\Service\Letter\SectionRenderer\SectionRendererPluginManagerFactory::class,

            // Letter Preview Service
            \Dvsa\Olcs\Api\Service\Letter\LetterPreviewService::class =>
                \Dvsa\Olcs\Api\Service\Letter\LetterPreviewServiceFactory::class,

            \Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClient::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClientFactory::class,
            \Dvsa\Olcs\Api\Rbac\IdentityProviderInterface::class => \Dvsa\Olcs\Api\Rbac\IdentityProviderFactory::class,
            \Dvsa\Olcs\Api\Rbac\JWTIdentityProvider::class => \Dvsa\Olcs\Api\Rbac\JWTIdentityProviderFactory::class,
            \Dvsa\Olcs\CompaniesHouse\Service\Client::class => \Dvsa\Olcs\CompaniesHouse\Service\ClientFactory::class,
            'TransExchangeXmlMapping' =>
                \Dvsa\Olcs\Api\Service\Ebsr\Mapping\TransExchangeXmlFactory::class,
            'TransExchangePublisherXmlMapping' =>
                \Dvsa\Olcs\Api\Service\Ebsr\Mapping\TransExchangePublisherXmlFactory::class,
            ApiSrv\Ebsr\FileProcessorInterface::class =>
                ApiSrv\Ebsr\FileProcessorFactory::class,
            ApiSrv\Ebsr\FileProcessor::class =>
                ApiSrv\Ebsr\FileProcessorFactory::class,
            \Dvsa\Olcs\Api\Service\Ebsr\InputFilter\XmlStructureInputFactory::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\InputFilter\XmlStructureInputFactory::class,
            \Dvsa\Olcs\Api\Service\Ebsr\InputFilter\BusRegistrationInputFactory::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\InputFilter\BusRegistrationInputFactory::class,
            \Dvsa\Olcs\Api\Service\Ebsr\InputFilter\ProcessedDataInputFactory::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\InputFilter\ProcessedDataInputFactory::class,
            \Dvsa\Olcs\Api\Service\Ebsr\InputFilter\ShortNoticeInputFactory::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\InputFilter\ShortNoticeInputFactory::class,
            'TrafficAreaValidator' => \Dvsa\Olcs\Api\Domain\Service\TrafficAreaValidator::class,

            'ComplianceEpisodeInput' => \Dvsa\Olcs\Api\Service\Nr\InputFilter\ComplianceEpisodeInputFactory::class,
            'ComplianceXmlStructure' => \Dvsa\Olcs\Api\Service\Nr\InputFilter\XmlStructureInputFactory::class,
            'SeriousInfringementInput' => \Dvsa\Olcs\Api\Service\Nr\InputFilter\SeriousInfringementInputFactory::class,
            'ComplianceEpisodeXmlMapping' => \Dvsa\Olcs\Api\Service\Nr\Mapping\ComplianceEpisodeXmlFactory::class,

            \Dvsa\Olcs\Api\Service\Nr\InrClientInterface::class => Dvsa\Olcs\Api\Service\Nr\InrClientFactory::class,
            \Dvsa\Olcs\Api\Service\Nr\CheckGoodRepute::class => \Dvsa\Olcs\Api\Service\Nr\CheckGoodReputeFactory::class,
            \Dvsa\Olcs\Api\Service\Nr\MsiResponse::class => \Dvsa\Olcs\Api\Service\Nr\MsiResponseFactory::class,

            \Dvsa\Olcs\Api\Mvc\OlcsBlameableListener::class => \Dvsa\Olcs\Api\Mvc\OlcsBlameableListenerFactory::class,
            \Dvsa\Olcs\Api\Listener\OlcsEntityListener::class => \Dvsa\Olcs\Api\Listener\OlcsEntityListener::class,

            ApiSrv\Nysiis\NysiisRestClient::class => ApiSrv\Nysiis\NysiisRestClientFactory::class,

            ApiSrv\Document\PrintLetter::class => ApiSrv\Document\PrintLetter::class,
            \Dvsa\Olcs\Api\Service\Toggle\ToggleService::class =>
                \Dvsa\Olcs\Api\Service\Toggle\ToggleServiceFactory::class,

            'TemplateDatabaseTwigLoader' => ApiSrv\Template\DatabaseTwigLoaderFactory::class,
            'TemplateDatabaseTemplateFetcher' => ApiSrv\Template\DatabaseTemplateFetcherFactory::class,
            'TemplateTwigRenderer' => ApiSrv\Template\TwigRendererFactory::class,
            'TemplateTwigEnvironment' => ApiSrv\Template\TwigEnvironmentFactory::class,
            'TemplateStrategySelectingViewRenderer' => ApiSrv\Template\StrategySelectingViewRendererFactory::class,

            'QaGenericAnswerSaver' => ApiSrv\Qa\Structure\Element\GenericAnswerSaverFactory::class,
            'QaBaseAnswerSaver' => ApiSrv\Qa\Structure\Element\BaseAnswerSaverFactory::class,
            'QaGenericAnswerClearer' => ApiSrv\Qa\Structure\Element\GenericAnswerClearerFactory::class,
            'QaCheckboxAnswerSaver' => ApiSrv\Qa\Structure\Element\Checkbox\CheckboxAnswerSaverFactory::class,
            'QaDateAnswerSaver' => ApiSrv\Qa\Structure\Element\Date\DateAnswerSaverFactory::class,
            'QaGenericAnswerProvider' => ApiSrv\Qa\AnswerSaver\GenericAnswerProviderFactory::class,
            'QaGenericAnswerWriter' => ApiSrv\Qa\AnswerSaver\GenericAnswerWriterFactory::class,
            'QaGenericAnswerFetcher' => ApiSrv\Qa\Structure\Element\GenericAnswerFetcherFactory::class,
            'QaApplicationAnswersClearer' => ApiSrv\Qa\AnswerSaver\ApplicationAnswersClearerFactory::class,

            'QaContextGenerator' => ApiSrv\Qa\QaContextGeneratorFactory::class,
            'QaElementGeneratorContextGenerator'
                => ApiSrv\Qa\Structure\Element\ElementGeneratorContextGeneratorFactory::class,
            'QaEntityProvider' => ApiSrv\Qa\QaEntityProviderFactory::class,
            'QaApplicationStepGenerator' => ApiSrv\Qa\Structure\ApplicationStepGeneratorFactory::class,
            'QaCheckboxElementGenerator' => ApiSrv\Qa\Structure\Element\Checkbox\CheckboxGeneratorFactory::class,
            'QaFilteredTranslateableTextGenerator' => ApiSrv\Qa\Structure\FilteredTranslateableTextGeneratorFactory::class,
            'QaQuestionTextGenerator' => ApiSrv\Qa\Structure\QuestionText\QuestionTextGeneratorFactory::class,
            'QaEcmtRemovalNoOfPermitsQuestionTextGenerator' => ApiSrv\Qa\Structure\QuestionText\Custom\EcmtRemovalNoOfPermitsGeneratorFactory::class,
            'QaEcmtRestrictedCountriesQuestionTextGenerator'
                => ApiSrv\Qa\Structure\QuestionText\Custom\Ecmt\RestrictedCountriesGeneratorFactory::class,
            'QaBilateralPermitUsageQuestionTextGenerator'
                => ApiSrv\Qa\Structure\QuestionText\Custom\Bilateral\PermitUsageGeneratorFactory::class,
            'QaBilateralCabotageQuestionTextGenerator'
                => ApiSrv\Qa\Structure\QuestionText\Custom\Bilateral\CabotageGeneratorFactory::class,

            'QaSelfservePageGenerator' => ApiSrv\Qa\Structure\SelfservePageGeneratorFactory::class,
            'QaFormFragmentGenerator' => ApiSrv\Qa\Structure\FormFragmentGeneratorFactory::class,
            'QaTextElementGenerator' => ApiSrv\Qa\Structure\Element\Text\TextGeneratorFactory::class,
            'QaRadioElementGenerator' => ApiSrv\Qa\Structure\Element\Radio\RadioGeneratorFactory::class,
            'QaDateElementGenerator' => ApiSrv\Qa\Structure\Element\Date\DateGeneratorFactory::class,
            'QaTotAuthVehiclesTextElementGenerator' => ApiSrv\Qa\Structure\Element\Text\Custom\TotAuthVehiclesGeneratorFactory::class,
            'QaTranslateableTextGenerator' => ApiSrv\Qa\Structure\TranslateableTextGeneratorFactory::class,
            'QaTranslateableTextParameterGenerator' => ApiSrv\Qa\Structure\TranslateableTextParameterGeneratorFactory::class,
            'QaJsonDecodingFilteredTranslateableTextGenerator'
                => ApiSrv\Qa\Structure\JsonDecodingFilteredTranslateableTextGeneratorFactory::class,
            'QaValidatorGenerator' => ApiSrv\Qa\Structure\ValidatorGeneratorFactory::class,
            'QaValidatorListGenerator' => ApiSrv\Qa\Structure\ValidatorListGeneratorFactory::class,
            'QaOptionListGenerator' => ApiSrv\Qa\Structure\Element\Options\OptionListGeneratorFactory::class,
            'QaRefDataOptionsSource' => ApiSrv\Qa\Structure\Element\Options\RefDataSourceFactory::class,
            'QaEcmtPermitUsageThreeOptionsRefDataOptionsSource'
                => ApiSrv\Qa\Structure\Element\Options\EcmtPermitUsageThreeOptionsRefDataSourceFactory::class,
            'QaEcmtPermitUsageFourOptionsRefDataOptionsSource'
                => ApiSrv\Qa\Structure\Element\Options\EcmtPermitUsageFourOptionsRefDataSourceFactory::class,
            'QaRepoQueryOptionsSource' => ApiSrv\Qa\Structure\Element\Options\RepoQuerySourceFactory::class,

            'QaEcmtRemovalNoOfPermitsAnswerWriter' =>
                ApiSrv\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits\AnswerWriterFactory::class,
            'QaEcmtRemovalNoOfPermitsAnswerSaver' =>
                ApiSrv\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits\NoOfPermitsAnswerSaverFactory::class,
            'QaEcmtRemovalNoOfPermitsAnswerClearer' =>
                ApiSrv\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits\NoOfPermitsAnswerClearerFactory::class,
            'QaEcmtRemovalNoOfPermitsFeeCreator' =>
                ApiSrv\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits\FeeCreatorFactory::class,
            'QaEcmtNoOfPermitsAnswerFetcher' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\NoOfPermitsAnswerFetcherFactory::class,
            'QaEcmtNoOfPermitsAnswerSaver' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\NoOfPermitsAnswerSaverFactory::class,
            'QaEcmtNoOfPermitsAnswerClearer' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\NoOfPermitsAnswerClearerFactory::class,
            'QaEcmtNoOfPermitsElementGenerator' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\NoOfPermitsGeneratorFactory::class,
            'QaEcmtEmissionsCategoryConditionalAdder' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\EmissionsCategoryConditionalAdderFactory::class,
            'QaEcmtConditionalFeeUpdater' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\ConditionalFeeUpdaterFactory::class,
            'QaEcmtFeeUpdater' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\FeeUpdaterFactory::class,
            'QaEcmtIntJourneysAnswerSaver' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\IntJourneysAnswerSaverFactory::class,
            'QaEcmtIntJourneysAnswerClearer' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\IntJourneysAnswerClearerFactory::class,
            'QaEcmtIntJourneysElementGenerator' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\IntJourneysGeneratorFactory::class,
            'QaEcmtAnnualTripsAbroadElementGenerator' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\AnnualTripsAbroadGeneratorFactory::class,
            'QaEcmtRestrictedCountriesAnswerSaver' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\RestrictedCountriesAnswerSaverFactory::class,
            'QaEcmtRestrictedCountriesAnswerClearer' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\RestrictedCountriesAnswerClearerFactory::class,
            'QaEcmtRestrictedCountriesElementGenerator' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\RestrictedCountriesGeneratorFactory::class,
            'QaEcmtAnnualTripsAbroadAnswerSaver' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\AnnualTripsAbroadAnswerSaverFactory::class,
            'QaEcmtSectorsAnswerSaver' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\SectorsAnswerSaverFactory::class,
            'QaEcmtSectorsAnswerClearer' =>
                ApiSrv\Qa\Structure\Element\Custom\Ecmt\SectorsAnswerClearerFactory::class,
            'QaCommonCertificatesAnswerSaver' =>
                ApiSrv\Qa\Structure\Element\Custom\Common\CertificatesAnswerSaverFactory::class,
            'QaCommonDateWithThresholdElementGenerator' =>
                ApiSrv\Qa\Structure\Element\Custom\Common\DateWithThresholdGeneratorFactory::class,
            'QaEcmtRemovalPermitStartDateElementGenerator' =>
                ApiSrv\Qa\Structure\Element\Custom\EcmtRemoval\PermitStartDateGeneratorFactory::class,
            'QaCertRoadworthinessMotExpiryDateElementGenerator' =>
                ApiSrv\Qa\Structure\Element\Custom\CertRoadworthiness\MotExpiryDateGeneratorFactory::class,
            'QaBilateralPermitUsageAnswerSaver' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\PermitUsageAnswerSaverFactory::class,
            'QaBilateralPermitUsageGenerator' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\PermitUsageGeneratorFactory::class,

            'QaBilateralCountryDeletingAnswerSaver' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\CountryDeletingAnswerSaverFactory::class,
            'QaBilateralCabotageOnlyElementGenerator' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\CabotageOnlyGeneratorFactory::class,
            'QaBilateralCabotageOnlyAnswerSaver' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\CabotageOnlyAnswerSaverFactory::class,
            'QaBilateralStandardAndCabotageElementGenerator' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\StandardAndCabotageGeneratorFactory::class,
            'QaBilateralStandardAndCabotageAnswerSaver' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\StandardAndCabotageAnswerSaverFactory::class,
            'QaBilateralThirdCountryElementGenerator' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\ThirdCountryGeneratorFactory::class,
            'QaBilateralThirdCountryAnswerSaver' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\ThirdCountryAnswerSaverFactory::class,
            'QaBilateralEmissionsStandardsElementGenerator' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\EmissionsStandardsGeneratorFactory::class,
            'QaBilateralEmissionsStandardsAnswerSaver' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\EmissionsStandardsAnswerSaverFactory::class,
            'QaBilateralNoOfPermitsElementGenerator' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsGeneratorFactory::class,
            'QaBilateralNoOfPermitsMoroccoElementGenerator' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsMoroccoGeneratorFactory::class,
            'QaBilateralClientReturnCodeHandler' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\ClientReturnCodeHandlerFactory::class,
            'QaBilateralNoOfPermitsAnswerSaver' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsAnswerSaverFactory::class,
            'QaBilateralNoOfPermitsMoroccoAnswerSaver' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsMoroccoAnswerSaverFactory::class,
            'QaBilateralNoOfPermitsAnswerClearer' =>
                ApiSrv\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsAnswerClearerFactory::class,

            'QaSupplementedApplicationStepsProvider' =>
                ApiSrv\Qa\Facade\SupplementedApplicationSteps\SupplementedApplicationStepsProviderFactory::class,

            'QaIrhpApplicationPostSubmitHandler' =>
                ApiSrv\Qa\PostSubmit\IrhpApplicationPostSubmitHandlerFactory::class,

            'PermitsAnswersSummaryGenerator' =>
                ApiSrv\Permits\AnswersSummary\AnswersSummaryGeneratorFactory::class,
            'PermitsIpaAnswersSummaryGenerator' =>
                ApiSrv\Permits\AnswersSummary\IpaAnswersSummaryGeneratorFactory::class,
            'PermitsHeaderAnswersSummaryRowsAdder' =>
                ApiSrv\Permits\AnswersSummary\HeaderAnswersSummaryRowsAdderFactory::class,
            'QaAnswersSummaryRowGenerator' =>
                ApiSrv\Qa\AnswersSummary\AnswersSummaryRowGeneratorFactory::class,
            'PermitsBilateralAnswersSummaryRowsAdder' =>
                ApiSrv\Permits\AnswersSummary\BilateralAnswersSummaryRowsAdderFactory::class,
            'PermitsBilateralIpaAnswersSummaryRowsAdder' =>
                ApiSrv\Permits\AnswersSummary\BilateralIpaAnswersSummaryRowsAdderFactory::class,
            'PermitsMultilateralAnswersSummaryRowsAdder' =>
                ApiSrv\Permits\AnswersSummary\MultilateralAnswersSummaryRowsAdderFactory::class,
            'QaAnswersSummaryRowsAdder' =>
                ApiSrv\Qa\AnswersSummary\AnswersSummaryRowsAdderFactory::class,
            'QaRadioAnswerSummaryProvider' =>
                ApiSrv\Qa\Structure\Element\Radio\RadioAnswerSummaryProviderFactory::class,

            'PermitsBilateralApplicationCountryRemover' =>
                ApiSrv\Permits\Bilateral\ApplicationCountryRemoverFactory::class,
            'PermitsBilateralApplicationFeesClearer' =>
                ApiSrv\Permits\Bilateral\ApplicationFeesClearerFactory::class,
            'PermitsAvailabilityWindowAvailabilityChecker' =>
                ApiSrv\Permits\Availability\WindowAvailabilityCheckerFactory::class,
            'PermitsAvailabilityStockAvailabilityChecker' =>
                ApiSrv\Permits\Availability\StockAvailabilityCheckerFactory::class,
            'PermitsAvailabilityStockAvailabilityCounter' =>
                ApiSrv\Permits\Availability\StockAvailabilityCounterFactory::class,
            'PermitsAvailabilityEmissionsCategoryAvailabilityChecker' =>
                ApiSrv\Permits\Availability\EmissionsCategoryAvailabilityCheckerFactory::class,
            'PermitsAvailabilityEmissionsCategoryAvailabilityCounter' =>
                ApiSrv\Permits\Availability\EmissionsCategoryAvailabilityCounterFactory::class,
            'PermitsAvailabilityCandidatePermitsAvailableCountCalculator' =>
                ApiSrv\Permits\Availability\CandidatePermitsAvailableCountCalculatorFactory::class,
            'PermitsAvailabilityCandidatePermitsGrantabilityChecker' =>
                ApiSrv\Permits\Availability\CandidatePermitsGrantabilityCheckerFactory::class,
            'PermitsAvailabilityEmissionsCategoriesGrantabilityChecker' =>
                ApiSrv\Permits\Availability\EmissionsCategoriesGrantabilityCheckerFactory::class,
            'PermitsAvailabilityStockLicenceMaxPermittedCounter' =>
                ApiSrv\Permits\Availability\StockLicenceMaxPermittedCounterFactory::class,

            'PermitsGrantabilityChecker' =>
                ApiSrv\Permits\GrantabilityCheckerFactory::class,
            'PermitsScoringCandidatePermitsCreator'
                => ApiSrv\Permits\Scoring\CandidatePermitsCreatorFactory::class,
            'PermitsScoringSuccessfulCandidatePermitsGenerator'
                => ApiSrv\Permits\Scoring\SuccessfulCandidatePermitsGeneratorFactory::class,
            'PermitsScoringSuccessfulCandidatePermitsWriter'
                => ApiSrv\Permits\Scoring\SuccessfulCandidatePermitsWriterFactory::class,
            'PermitsScoringEmissionsCategoryAvailabilityCounter'
                => ApiSrv\Permits\Scoring\EmissionsCategoryAvailabilityCounterFactory::class,
            'PermitsScoringSuccessfulCandidatePermitsFacade'
                => ApiSrv\Permits\Scoring\SuccessfulCandidatePermitsFacadeFactory::class,
            'PermitsApplyRangesStockBasedForCpProviderFactory'
                => ApiSrv\Permits\ApplyRanges\StockBasedForCpProviderFactoryFactory::class,
            'PermitsCommonStockBasedRestrictedCountryIdsProvider'
                => ApiSrv\Permits\Common\StockBasedRestrictedCountryIdsProviderFactory::class,
            'PermitsCommonStockBasedPermitTypeConfigProvider'
                => ApiSrv\Permits\Common\StockBasedPermitTypeConfigProviderFactory::class,
            'PermitsCommonRangeBasedRestrictedCountriesProvider'
                => ApiSrv\Permits\Common\RangeBasedRestrictedCountriesProviderFactory::class,
            'PermitsCommonTypeBasedPermitTypeConfigProvider'
                => ApiSrv\Permits\Common\TypeBasedPermitTypeConfigProviderFactory::class,
            'PermitsCandidatePermitsApggCandidatePermitsCreator'
                => ApiSrv\Permits\CandidatePermits\ApggCandidatePermitsCreatorFactory::class,
            'PermitsCandidatePermitsApggEmissionsCatCandidatePermitsCreator'
                => ApiSrv\Permits\CandidatePermits\ApggEmissionsCatCandidatePermitsCreatorFactory::class,
            'PermitsCandidatePermitsIrhpCandidatePermitsCreator'
                => ApiSrv\Permits\CandidatePermits\IrhpCandidatePermitsCreatorFactory::class,

            'PermitsCheckableCheckedValueUpdater'
                => ApiSrv\Permits\Checkable\CheckedValueUpdaterFactory::class,
            'PermitsCheckableCreateTaskCommandGenerator'
                => ApiSrv\Permits\Checkable\CreateTaskCommandGeneratorFactory::class,

            'PermitsBilateralFeeBreakdownGenerator'
                => ApiSrv\Permits\FeeBreakdown\BilateralFeeBreakdownGeneratorFactory::class,

            'PermitsAllocateIrhpPermitAllocator' => ApiSrv\Permits\Allocate\IrhpPermitAllocatorFactory::class,

            'PermitsBilateralInternalApplicationCountryUpdater'
                => ApiSrv\Permits\Bilateral\Internal\ApplicationCountryUpdaterFactory::class,
            'PermitsBilateralInternalApplicationUpdater'
                => ApiSrv\Permits\Bilateral\Internal\ApplicationUpdaterFactory::class,
            'PermitsBilateralInternalExistingIrhpPermitApplicationHandler'
                => ApiSrv\Permits\Bilateral\Internal\ExistingIrhpPermitApplicationHandlerFactory::class,
            'PermitsBilateralInternalIrhpPermitApplicationCreator'
                => ApiSrv\Permits\Bilateral\Internal\IrhpPermitApplicationCreatorFactory::class,
            'PermitsBilateralInternalQuestionHandlerDelegator'
                => ApiSrv\Permits\Bilateral\Internal\QuestionHandlerDelegatorFactory::class,
            'PermitsBilateralInternalCabotageOnlyQuestionHandler'
                => ApiSrv\Permits\Bilateral\Internal\CabotageOnlyQuestionHandlerFactory::class,
            'PermitsBilateralInternalEmissionsStandardsQuestionHandler'
                => ApiSrv\Permits\Bilateral\Internal\EmissionsStandardsQuestionHandlerFactory::class,
            'PermitsBilateralInternalThirdCountryQuestionHandler'
                => ApiSrv\Permits\Bilateral\Internal\ThirdCountryQuestionHandlerFactory::class,
            'PermitsBilateralInternalNumberOfPermitsQuestionHandler'
                => ApiSrv\Permits\Bilateral\Internal\NumberOfPermitsQuestionHandlerFactory::class,
            'PermitsBilateralInternalNumberOfPermitsMoroccoQuestionHandler'
                => ApiSrv\Permits\Bilateral\Internal\NumberOfPermitsMoroccoQuestionHandlerFactory::class,
            'PermitsBilateralInternalPermitUsageQuestionHandler'
                => ApiSrv\Permits\Bilateral\Internal\PermitUsageQuestionHandlerFactory::class,
            'PermitsBilateralInternalStandardAndCabotageQuestionHandler'
                => ApiSrv\Permits\Bilateral\Internal\StandardAndCabotageQuestionHandlerFactory::class,

            'PermitsBilateralCommonNoOfPermitsUpdater'
                => ApiSrv\Permits\Bilateral\Common\NoOfPermitsUpdaterFactory::class,
            'PermitsBilateralCommonNoOfPermitsConditionalUpdater'
                => ApiSrv\Permits\Bilateral\Common\NoOfPermitsConditionalUpdaterFactory::class,
            'PermitsBilateralCommonPermitUsageUpdater'
                => ApiSrv\Permits\Bilateral\Common\PermitUsageUpdaterFactory::class,
            'PermitsBilateralCommonStandardAndCabotageUpdater'
                => ApiSrv\Permits\Bilateral\Common\StandardAndCabotageUpdaterFactory::class,
            'PermitsBilateralCommonModifiedAnswerUpdater'
                => ApiSrv\Permits\Bilateral\Common\ModifiedAnswerUpdaterFactory::class,

            'PermitsBilateralMetadataCountryGenerator'
                => ApiSrv\Permits\Bilateral\Metadata\CountryGeneratorFactory::class,
            'PermitsBilateralMetadataPeriodArrayGenerator'
                => ApiSrv\Permits\Bilateral\Metadata\PeriodArrayGeneratorFactory::class,
            'PermitsBilateralMetadataPeriodGenerator'
                => ApiSrv\Permits\Bilateral\Metadata\PeriodGeneratorFactory::class,
            'PermitsBilateralMetadataStandardFieldsGenerator'
                => ApiSrv\Permits\Bilateral\Metadata\StandardFieldsGeneratorFactory::class,

            'PermitsFeesEcmtApplicationFeeCommandCreator'
                => ApiSrv\Permits\Fees\EcmtApplicationFeeCommandCreatorFactory::class,
            'PermitsFeesDaysToPayIssueFeeProvider'
                => ApiSrv\Permits\Fees\DaysToPayIssueFeeProviderFactory::class,

            'EventHistoryCreator' =>
                ApiSrv\EventHistory\CreatorFactory::class,
            ApiSrv\EventHistory\Creator::class => ApiSrv\EventHistory\CreatorFactory::class,
            ApiSrv\AccessToken\Provider::class => ApiSrv\AccessToken\ProviderFactory::class,
            ApiSrv\Ebsr\S3Processor::class => ApiSrv\Ebsr\S3ProcessorFactory::class,

            ApiSrv\GovUkAccount\GovUkAccountService::class => ApiSrv\GovUkAccount\GovUkAccountServiceFactory::class,

            ApiSrv\AddressHelper\AddressHelperService::class => ApiSrv\AddressHelper\AddressHelperServiceFactory::class,

            Aws\S3\S3Client::class => Dvsa\Olcs\Api\Service\S3\S3ClientFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'submission' => __DIR__ . '/../view/submission',
            'answers-summary' => __DIR__ . '/../view/permits/',
        ]
    ],
    'file_uploader' => [
        'default' => 'ContentStore',
        'config' => [
            'location' => 'documents',
            'defaultPath' => '[locale]/[doc_type_name]/[year]/[month]', // e.g. gb/publications/2015/03
        ]
    ],
    'controller_plugins' => [
        'invokables' => [
            'response' => \Dvsa\Olcs\Api\Mvc\Controller\Plugin\Response::class,
        ]
    ],
    'controllers' => [
        'invokables' => [
        ],
        'factories' => [
            'Api\Generic' => \Dvsa\Olcs\Api\Controller\GenericControllerFactory::class,
            'Api\Xml' => \Dvsa\Olcs\Api\Controller\XmlControllerFactory::class
        ]
    ],
    \Dvsa\Olcs\Api\Domain\DbQueryServiceManagerFactory::CONFIG_KEY => include(__DIR__ . '/db-query-map.config.php'),
    \Dvsa\Olcs\Api\Domain\CommandHandlerManagerFactory::CONFIG_KEY => [
        'factories' => require(__DIR__ . '/command-map.config.php')
    ],
    \Dvsa\Olcs\Api\Domain\QueryHandlerManagerFactory::CONFIG_KEY => [
        'factories' => require(__DIR__ . '/query-map.config.php')
    ],
    \Dvsa\Olcs\Api\Domain\ValidationHandlerManagerFactory::CONFIG_KEY => [
        'factories' => require(__DIR__ . '/validation-map.config.php')
    ],
    \Dvsa\Olcs\Api\Domain\ValidatorManagerFactory::CONFIG_KEY => require(__DIR__ . '/validators.config.php'),
    \Dvsa\Olcs\Api\Domain\QueryPartialServiceManagerFactory::CONFIG_KEY => [
        'factories' => [
            QueryPartial\WithApplication::class => QueryPartial\WithApplicationFactory::class,
            QueryPartial\WithIrhpApplication::class => QueryPartial\WithIrhpApplicationFactory::class,
            QueryPartial\WithBusReg::class => QueryPartial\WithBusRegFactory::class,
            QueryPartial\WithContactDetails::class => QueryPartial\WithContactDetailsFactory::class,
            QueryPartial\WithCase::class => QueryPartial\WithCaseFactory::class,
            QueryPartial\WithCreatedBy::class => QueryPartial\WithCreatedByFactory::class,
            QueryPartial\WithRefdata::class => QueryPartial\WithRefdataFactory::class,
            QueryPartial\WithUser::class => QueryPartial\WithUserFactory::class,
            QueryPartial\WithPersonContactDetails::class => QueryPartial\WithPersonContactDetailsFactory::class,
            QueryPartial\WithCreatedByWithTeam::class => QueryPartial\WithCreatedByWithTeamFactory::class,
        ],
        'invokables' => [
            'byId' => QueryPartial\ById::class,
            'with' => QueryPartial\With::class,
            'paginate' => QueryPartial\Paginate::class,
            'order' => QueryPartial\Order::class,
            'filterByLicence' => QueryPartial\Filter\ByLicence::class,
            'filterByApplication' => QueryPartial\Filter\ByApplication::class,
            'filterByBusReg' => QueryPartial\Filter\ByBusReg::class,
            'filterByIds' => QueryPartial\Filter\ByIds::class,
        ],
        'aliases' => [
            'withApplication' => QueryPartial\WithApplication::class,
            'withIrhpApplication' => QueryPartial\WithIrhpApplication::class,
            'withBusReg' => QueryPartial\WithBusReg::class,
            'withContactDetails' => QueryPartial\WithContactDetails::class,
            'withCase' => QueryPartial\WithCase::class,
            'withCreatedBy' => QueryPartial\WithCreatedBy::class,
            'withRefdata' => QueryPartial\WithRefdata::class,
            'withUser' => QueryPartial\WithUser::class,
            'withPersonContactDetails' => QueryPartial\WithPersonContactDetails::class,
            'withCreatedByWithTeam' => QueryPartial\WithCreatedByWithTeam::class,
        ]
    ],
    \Dvsa\Olcs\Api\Domain\RepositoryServiceManagerFactory::CONFIG_KEY => [
        'factories' => [
            'Application' => RepositoryFactory::class,
            'Address' => RepositoryFactory::class,
            'Appeal' => RepositoryFactory::class,
            'ContactDetails' => RepositoryFactory::class,
            'CompanySubsidiary' => RepositoryFactory::class,
            'Conviction' => RepositoryFactory::class,
            'Decision' => RepositoryFactory::class,
            'Bus' => RepositoryFactory::class,
            'BusRegHistory' => RepositoryFactory::class,
            'BusRegOtherService' => RepositoryFactory::class,
            'BusNoticePeriod' => RepositoryFactory::class,
            'BusShortNotice' => RepositoryFactory::class,
            'BusServiceType' => RepositoryFactory::class,
            'LocalAuthority' => RepositoryFactory::class,
            'Trailer' => RepositoryFactory::class,
            'GracePeriod' => RepositoryFactory::class,
            'FeeType' => RepositoryFactory::class,
            'Fee' => RepositoryFactory::class,
            'Pi' => RepositoryFactory::class,
            'NonPi' => RepositoryFactory::class,
            'EventHistory' => RepositoryFactory::class,
            'EventHistoryType' => RepositoryFactory::class,
            'PublicHoliday' => RepositoryFactory::class,
            'Sla' => RepositoryFactory::class,
            Repository\SlaException::class => RepositoryFactory::class,
            Repository\PiSlaException::class => RepositoryFactory::class,
            'LicenceNoGen' => RepositoryFactory::class,
            'UserPasswordReset' => RepositoryFactory::class,
            'PreviousConviction' => RepositoryFactory::class,
            'Prohibition' => RepositoryFactory::class,
            'ProhibitionDefect' => RepositoryFactory::class,
            'LegacyOffence' => RepositoryFactory::class,
            'Note' => RepositoryFactory::class,
            'TradingName' => RepositoryFactory::class,
            'IrfoGvPermit' => RepositoryFactory::class,
            'IrfoGvPermitType' => RepositoryFactory::class,
            'IrfoPermitStock' => RepositoryFactory::class,
            'IrfoPsvAuth' => RepositoryFactory::class,
            'IrfoPsvAuthType' => RepositoryFactory::class,
            'IrfoPsvAuthNumber' => RepositoryFactory::class,
            'IrfoCountry' => RepositoryFactory::class,
            'Impounding' => RepositoryFactory::class,
            'CommunityLic' => RepositoryFactory::class,
            'Workshop' => RepositoryFactory::class,
            'FinancialStandingRate' => RepositoryFactory::class,
            'Complaint' => RepositoryFactory::class,
            'PhoneContact' => RepositoryFactory::class,
            'OtherLicence' => RepositoryFactory::class,
            Repository\Document::class => RepositoryFactory::class,
            Repository\Correspondence::class => RepositoryFactory::class,
            Repository\SystemParameter::class => RepositoryFactory::class,
            'FeatureToggle' => RepositoryFactory::class,
            'Stay' => RepositoryFactory::class,
            'Submission' => RepositoryFactory::class,
            Repository\TaskAllocationRule::class => RepositoryFactory::class,
            'TaskAlphaSplit' => RepositoryFactory::class,
            'IrfoPartner' => RepositoryFactory::class,
            'Transaction' => RepositoryFactory::class,
            'TransportManager' => RepositoryFactory::class,
            'DocParagraph' => RepositoryFactory::class,
            'Opposition' => RepositoryFactory::class,
            'Statement' => RepositoryFactory::class,
            'PublicationLink' => RepositoryFactory::class,
            'Publication' => RepositoryFactory::class,
            'GoodsDisc' => RepositoryFactory::class,
            'PsvDisc' => RepositoryFactory::class,
            'PiHearing' => RepositoryFactory::class,
            'Recipient' => RepositoryFactory::class,
            'Partner' => RepositoryFactory::class,
            'TransportManagerApplication' => RepositoryFactory::class,
            'TransportManagerLicence' => RepositoryFactory::class,
            'Person' => RepositoryFactory::class,
            'ApplicationOperatingCentre' => RepositoryFactory::class,
            'LicenceOperatingCentre' => RepositoryFactory::class,
            'TmCaseDecision' => RepositoryFactory::class,
            'TmEmployment' => RepositoryFactory::class,
            'TmQualification' => RepositoryFactory::class,
            'DocTemplate' => RepositoryFactory::class,
            'LicenceStatusRule' => RepositoryFactory::class,
            'LicenceVehicle' => RepositoryFactory::class,
            'CommunityLicSuspension' => RepositoryFactory::class,
            'CommunityLicSuspensionReason' => RepositoryFactory::class,
            'CommunityLicWithdrawal' => RepositoryFactory::class,
            'CommunityLicWithdrawalReason' => RepositoryFactory::class,
            'ConditionUndertaking' => RepositoryFactory::class,
            'OperatingCentre' => RepositoryFactory::class,
            'Category' => RepositoryFactory::class,
            'SubCategory' => RepositoryFactory::class,
            'SubCategoryDescription' => RepositoryFactory::class,
            'Scan' => RepositoryFactory::class,
            'BusRegBrowseView' => RepositoryFactory::class,
            'BusRegSearchView' => RepositoryFactory::class,
            'ProposeToRevoke' => RepositoryFactory::class,
            'OrganisationPerson' => RepositoryFactory::class,
            'Vehicle' => RepositoryFactory::class,
            'VehicleHistoryView' => RepositoryFactory::class,
            'InspectionRequest' => RepositoryFactory::class,
            'CorrespondenceInbox' => RepositoryFactory::class,
            'SubmissionAction' => RepositoryFactory::class,
            'SubmissionSectionComment' => RepositoryFactory::class,
            'TrafficArea' => RepositoryFactory::class,
            'ChangeOfEntity' => RepositoryFactory::class,
            'ApplicationOrganisationPerson' => RepositoryFactory::class,
            'DocumentSearchView' => RepositoryFactory::class,
            'DocTemplateSearchView' => RepositoryFactory::class,
            'S4' => RepositoryFactory::class,
            'TaskSearchView' => RepositoryFactory::class,
            'PrivateHireLicence' => RepositoryFactory::class,
            'Continuation' => RepositoryFactory::class,
            'ContinuationDetail' => RepositoryFactory::class,
            'CompaniesHouseAlert' => RepositoryFactory::class,
            'CompaniesHouseCompany' => RepositoryFactory::class,
            'CompaniesHouseInsolvencyPractitioner' => RepositoryFactory::class,
            'Queue' => RepositoryFactory::class,
            Repository\AdminAreaTrafficArea::class => RepositoryFactory::class,
            Repository\PostcodeEnforcementArea::class => RepositoryFactory::class,
            'Venue' => RepositoryFactory::class,
            'Disqualification' => RepositoryFactory::class,
            'DiscSequence' => RepositoryFactory::class,
            'EbsrSubmission' => RepositoryFactory::class,
            Repository\TxcInbox::class => RepositoryFactory::class,
            'OrganisationUser' => RepositoryFactory::class,
            'Role' => RepositoryFactory::class,
            'ApplicationReadAudit' => RepositoryFactory::class,
            'LicenceReadAudit' => RepositoryFactory::class,
            'OrganisationReadAudit' => RepositoryFactory::class,
            'BusRegReadAudit' => RepositoryFactory::class,
            'TransportManagerReadAudit' => RepositoryFactory::class,
            'CasesReadAudit' => RepositoryFactory::class,
            'Team' => RepositoryFactory::class,
            'TeamPrinter' => RepositoryFactory::class,
            'Printer' => RepositoryFactory::class,
            'ErruRequest' => RepositoryFactory::class,
            'ErruRequestFailure' => RepositoryFactory::class,
            'SeriousInfringement' => RepositoryFactory::class,
            'SiPenalty' => RepositoryFactory::class,
            'SiCategory' => RepositoryFactory::class,
            'SiCategoryType' => RepositoryFactory::class,
            'SiPenaltyRequestedType' => RepositoryFactory::class,
            'SiPenaltyImposedType' => RepositoryFactory::class,
            'SiPenaltyType' => RepositoryFactory::class,
            'Country' => RepositoryFactory::class,
            'PresidingTc' => RepositoryFactory::class,
            'RefData' => RepositoryFactory::class,
            'SlaTargetDate' => RepositoryFactory::class,
            'ViOcView' => RepositoryFactory::class,
            'ViOpView' => RepositoryFactory::class,
            'ViTnmView' => RepositoryFactory::class,
            'ViVhlView' => RepositoryFactory::class,
            'SystemInfoMessage' => RepositoryFactory::class,
            'Reason' => RepositoryFactory::class,
            'PiDefinition' => RepositoryFactory::class,
            'DataGovUk' => Repository\Factory\DataGovUkFactory::class,
            'DataDvaNi' => Repository\Factory\DataDvaNiFactory::class,
            'CompanyHouseVsOlcsDiffs' => Repository\Factory\CompaniesHouseVsOlcsDiffsFactory::class,
            'DigitalSignature' => RepositoryFactory::class,
            'DataRetentionRule' => RepositoryFactory::class,
            'DataRetention' => RepositoryFactory::class,
            'DataService' => RepositoryFactory::class,
            'DocumentToDelete' => RepositoryFactory::class,
            'Hearing' => RepositoryFactory::class,
            'GetDbValue' => RepositoryFactory::class,
            'Surrender' => RepositoryFactory::class,
            'Sectors' => RepositoryFactory::class,
            'IrhpPermitApplication' => RepositoryFactory::class,
            'IrhpApplication' => RepositoryFactory::class,
            'IrhpApplicationReadAudit' => RepositoryFactory::class,
            'IrhpCandidatePermit' => RepositoryFactory::class,
            'IrhpPermit' => RepositoryFactory::class,
            'IrhpPermitJurisdictionQuota' => RepositoryFactory::class,
            'IrhpPermitSectorQuota' => RepositoryFactory::class,
            'IrhpPermitStock' => RepositoryFactory::class,
            'IrhpPermitType' => RepositoryFactory::class,
            'IrhpPermitWindow' => RepositoryFactory::class,
            'IrhpPermitRange' => RepositoryFactory::class,
            'Template' => RepositoryFactory::class,
            'ApplicationStep' => RepositoryFactory::class,
            'Answer' => RepositoryFactory::class,
            'ApplicationPathGroup' => RepositoryFactory::class,
            'TranslationKey' => RepositoryFactory::class,
            'MessageFailures' => RepositoryFactory::class,
            'TranslationKeyText' => RepositoryFactory::class,
            'Language' => RepositoryFactory::class,
            'Replacement' => RepositoryFactory::class,
            Repository\Conversation::class => RepositoryFactory::class,
            Repository\Message::class => RepositoryFactory::class,
            Repository\MessageContent::class => RepositoryFactory::class,
            Repository\MessagingConversation::class => RepositoryFactory::class,
            Repository\Licence::class => RepositoryFactory::class,
            Repository\Application::class => RepositoryFactory::class,
            Repository\Cases::class => RepositoryFactory::class,
            Repository\MessagingSubject::class => RepositoryFactory::class,
            Repository\Organisation::class => RepositoryFactory::class,
            Repository\Task::class => RepositoryFactory::class,
            Repository\MessagingUserMessageRead::class => RepositoryFactory::class,
            Repository\User::class => RepositoryFactory::class,
            Repository\Bus::class => RepositoryFactory::class,
            // Letter repositories
            Repository\LetterType::class => RepositoryFactory::class,
            Repository\LetterSection::class => RepositoryFactory::class,
            Repository\LetterSectionVersion::class => RepositoryFactory::class,
            Repository\LetterIssue::class => RepositoryFactory::class,
            Repository\LetterIssueVersion::class => RepositoryFactory::class,
            Repository\LetterTodo::class => RepositoryFactory::class,
            Repository\LetterTodoVersion::class => RepositoryFactory::class,
            Repository\LetterAppendix::class => RepositoryFactory::class,
            Repository\LetterAppendixVersion::class => RepositoryFactory::class,
            Repository\LetterInstance::class => RepositoryFactory::class,
            Repository\MasterTemplate::class => RepositoryFactory::class,
            Repository\LetterTestData::class => RepositoryFactory::class,
            Repository\LetterIssueType::class => RepositoryFactory::class,
            Repository\LetterTypeSection::class => RepositoryFactory::class,
            Repository\LetterTypeIssue::class => RepositoryFactory::class,
            Repository\LetterTypeTodo::class => RepositoryFactory::class,
            Repository\LetterTypeAppendix::class => RepositoryFactory::class,
            Repository\LetterInstanceSection::class => RepositoryFactory::class,
            Repository\LetterInstanceIssue::class => RepositoryFactory::class,
            Repository\LetterInstanceTodo::class => RepositoryFactory::class,
            Repository\LetterInstanceAppendix::class => RepositoryFactory::class
        ],
        'aliases' => [
            'Conversation' => Repository\Conversation::class,
            'Message' => Repository\Message::class,
            'MessageContent' => Repository\MessageContent::class,
            'Licence' => Repository\Licence::class,
            'Application' => Repository\Application::class,
            'Organisation' => Repository\Organisation::class,
            'Task' => Repository\Task::class,
            'User' => Repository\User::class,
            'Document' => Repository\Document::class,
            'Cases' => Repository\Cases::class,
            'SystemParameter' => Repository\SystemParameter::class,
            'TaskAllocationRule' => Repository\TaskAllocationRule::class,
            'TxcInbox' => Repository\TxcInbox::class,
            'Correspondence' => Repository\Correspondence::class,
            'PostcodeEnforcementArea' => Repository\PostcodeEnforcementArea::class,
            'AdminAreaTrafficArea' => Repository\AdminAreaTrafficArea::class,
            'SlaException' => Repository\SlaException::class,
            'PiSlaException' => Repository\PiSlaException::class,
            // Letter repository aliases
            'LetterType' => Repository\LetterType::class,
            'LetterSection' => Repository\LetterSection::class,
            'LetterSectionVersion' => Repository\LetterSectionVersion::class,
            'LetterIssue' => Repository\LetterIssue::class,
            'LetterIssueVersion' => Repository\LetterIssueVersion::class,
            'LetterTodo' => Repository\LetterTodo::class,
            'LetterTodoVersion' => Repository\LetterTodoVersion::class,
            'LetterAppendix' => Repository\LetterAppendix::class,
            'LetterAppendixVersion' => Repository\LetterAppendixVersion::class,
            'LetterInstance' => Repository\LetterInstance::class,
            'MasterTemplate' => Repository\MasterTemplate::class,
            'LetterTestData' => Repository\LetterTestData::class,
            'LetterIssueType' => Repository\LetterIssueType::class,
            'LetterTypeSection' => Repository\LetterTypeSection::class,
            'LetterTypeIssue' => Repository\LetterTypeIssue::class,
            'LetterTypeTodo' => Repository\LetterTypeTodo::class,
            'LetterTypeAppendix' => Repository\LetterTypeAppendix::class,
            'LetterInstanceSection' => Repository\LetterInstanceSection::class,
            'LetterInstanceIssue' => Repository\LetterInstanceIssue::class,
            'LetterInstanceTodo' => Repository\LetterInstanceTodo::class,
            'LetterInstanceAppendix' => Repository\LetterInstanceAppendix::class,
        ],
    ],
    \Dvsa\Olcs\Api\Domain\FormControlServiceManagerFactory::CONFIG_KEY => [
        'factories' => [
            Question::FORM_CONTROL_TYPE_CHECKBOX => QaStrategy\CheckboxFormControlStrategyFactory::class,
            Question::FORM_CONTROL_TYPE_TEXT => QaStrategy\TextFormControlStrategyFactory::class,
            Question::FORM_CONTROL_TYPE_RADIO => QaStrategy\RadioFormControlStrategyFactory::class,
            Question::FORM_CONTROL_ECMT_REMOVAL_NO_OF_PERMITS =>
                QaStrategy\EcmtRemovalNoOfPermitsFormControlStrategyFactory::class,
            Question::FORM_CONTROL_ECMT_REMOVAL_PERMIT_START_DATE =>
                QaStrategy\EcmtRemovalPermitStartDateFormControlStrategyFactory::class,
            Question::FORM_CONTROL_ECMT_NO_OF_PERMITS_EITHER =>
                QaStrategy\EcmtNoOfPermitsEitherFormControlStrategyFactory::class,
            Question::FORM_CONTROL_ECMT_NO_OF_PERMITS_BOTH =>
                QaStrategy\EcmtNoOfPermitsBothFormControlStrategyFactory::class,
            Question::FORM_CONTROL_ECMT_PERMIT_USAGE =>
                QaStrategy\EcmtPermitUsageFormControlStrategyFactory::class,
            Question::FORM_CONTROL_ECMT_INTERNATIONAL_JOURNEYS =>
                QaStrategy\EcmtIntJourneysFormControlStrategyFactory::class,
            Question::FORM_CONTROL_ECMT_RESTRICTED_COUNTRIES =>
                QaStrategy\EcmtRestrictedCountriesFormControlStrategyFactory::class,
            Question::FORM_CONTROL_ECMT_ANNUAL_TRIPS_ABROAD =>
                QaStrategy\EcmtAnnualTripsAbroadFormControlStrategyFactory::class,
            Question::FORM_CONTROL_ECMT_SECTORS =>
                QaStrategy\EcmtSectorsFormControlStrategyFactory::class,
            Question::FORM_CONTROL_ECMT_CHECK_ECMT_NEEDED =>
                QaStrategy\EcmtCheckEcmtNeededFormControlStrategyFactory::class,
            Question::FORM_CONTROL_ECMT_SHORT_TERM_EARLIEST_PERMIT_DATE =>
                QaStrategy\EcmtShortTermEarliestPermitDateFormControlStrategyFactory::class,
            Question::FORM_CONTROL_ECMT_ANNUAL_2018_NO_OF_PERMITS =>
                QaStrategy\TextFormControlStrategyFactory::class,
            Question::FORM_CONTROL_CERT_ROADWORTHINESS_MOT_EXPIRY_DATE =>
                QaStrategy\CertRoadworthinessMotExpiryDateFormControlStrategyFactory::class,
            Question::FORM_CONTROL_COMMON_CERTIFICATES =>
                QaStrategy\CommonCertificatesFormControlStrategyFactory::class,
            Question::FORM_CONTROL_BILATERAL_PERMIT_USAGE =>
                QaStrategy\BilateralPermitUsageFormControlStrategyFactory::class,
            Question::FORM_CONTROL_BILATERAL_CABOTAGE_ONLY =>
                QaStrategy\BilateralCabotageOnlyFormControlStrategyFactory::class,
            Question::FORM_CONTROL_BILATERAL_CABOTAGE_STD_AND_CABOTAGE =>
                QaStrategy\BilateralStandardAndCabotageFormControlStrategyFactory::class,
            Question::FORM_CONTROL_BILATERAL_NO_OF_PERMITS =>
                QaStrategy\BilateralNoOfPermitsFormControlStrategyFactory::class,
            Question::FORM_CONTROL_BILATERAL_THIRD_COUNTRY =>
                QaStrategy\BilateralThirdCountryFormControlStrategyFactory::class,
            Question::FORM_CONTROL_BILATERAL_EMISSIONS_STANDARDS =>
                QaStrategy\BilateralEmissionsStandardsFormControlStrategyFactory::class,
            Question::FORM_CONTROL_BILATERAL_NO_OF_PERMITS_MOROCCO =>
                QaStrategy\BilateralNoOfPermitsMoroccoFormControlStrategyFactory::class,
        ]
    ],
    'entity_namespaces' => include(__DIR__ . '/namespace.config.php'),
    'doctrine' => [
        'driver' => [
            'EntityDriver' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Entity'
                ]
            ],
            'translatable_metadata_driver' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [
                    'vendor/gedmo/doctrine-extensions/src/Translatable/Entity'
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    'Dvsa\Olcs\Api\Entity' => 'EntityDriver',
                    'Gedmo\Translatable\Entity' => 'translatable_metadata_driver'
                ]
            ]
        ],
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    \Dvsa\Olcs\Api\Listener\OlcsEntityListener::class,
                    \Gedmo\SoftDeleteable\SoftDeleteableListener::class,
                    \Gedmo\Translatable\TranslatableListener::class,
                    \Dvsa\Olcs\Api\Mvc\OlcsBlameableListener::class,
                ],
            ],
        ],
        'configuration' => [
            'orm_default' => [
                'filters' => [
                    'soft-deleteable' => \Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter::class,
                ],
                'types' => [
                    'yesno' => \Dvsa\Olcs\Api\Entity\Types\YesNoType::class,
                    'yesnonull' => \Dvsa\Olcs\Api\Entity\Types\YesNoNullType::class,
                    'date' => \Dvsa\Olcs\Api\Entity\Types\DateType::class,
                    'datetime' => \Dvsa\Olcs\Api\Entity\Types\DateTimeType::class,
                    'encrypted_string' => \Dvsa\Olcs\Api\Entity\Types\EncryptedStringType::class
                ]
            ]
        ]
    ],
    'lmc_rbac' => [
        'identity_provider' => \Dvsa\Olcs\Api\Rbac\IdentityProviderInterface::class,
        'role_provider' => [
            \LmcRbacMvc\Role\ObjectRepositoryRoleProvider::class => [
                'object_manager'     => 'doctrine.entitymanager.orm_default',
                'class_name'         => \Dvsa\Olcs\Api\Entity\User\Role::class,
                'role_name_property' => 'role'
            ]
        ],
        'assertion_map' => [
            'can-update-licence-licence-type' => \Dvsa\Olcs\Api\Assertion\Licence\UpdateLicenceType::class,
            'can-manage-user-selfserve' => \Dvsa\Olcs\Api\Assertion\User\ManageUserSelfserve::class,
            'can-read-user-selfserve' => \Dvsa\Olcs\Api\Assertion\User\ReadUserSelfserve::class,
        ]
    ],
    'permits' => [
        'types' => [
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT => [
                'restricted_country_ids' => ['AT', 'GR', 'HU', 'IT', 'RU'],
                'restricted_countries_question_key' => 'qanda.ecmt-annual.restricted-countries.question',
            ],
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM => [
                'restricted_country_ids' => ['GR', 'HU', 'IT', 'RU'],
                'restricted_countries_question_key' => 'qanda.ecmt-short-term.restricted-countries.question',
            ],
        ]
    ],
    'publications' => [
        'LicencePublication' => [
            'context' => [
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\BusNote::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Publication\PreviousLicencePublicationNo::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceAddress::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\People::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\TransportManagers::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceCancelled::class,
            ],
            'process' => [
                Dvsa\Olcs\Api\Service\Publication\Process\Licence\Text1::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Licence\Text2::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Licence\Text3::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Licence\Police::class,
            ],
        ],
        'ApplicationPublication' => [
            'context' => [
                Dvsa\Olcs\Api\Service\Publication\Context\Publication\PreviousApplicationPublicationNo::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Application\ConditionUndertaking::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Application\Authorisations::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Application\TransportManagers::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceAddress::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Application\People::class,
            ],
            'process' => [
                Dvsa\Olcs\Api\Service\Publication\Process\Application\Text1::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Application\Text2::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Application\Text3::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Application\Police::class
            ],
        ],
        'VariationPublication' => [
            'context' => [
                Dvsa\Olcs\Api\Service\Publication\Context\Publication\PreviousApplicationPublicationNo::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceAddress::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Variation\ConditionUndertaking::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Variation\OperatingCentres::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Variation\Authorisations::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Application\People::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Application\TransportManagers::class,
            ],
            'process' => [
                Dvsa\Olcs\Api\Service\Publication\Process\Application\Text1::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Variation\Text2::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Variation\Text3::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Application\Police::class,
            ],
        ],
        'Schedule41TruePublication' => [
            'context' => [
                Dvsa\Olcs\Api\Service\Publication\Context\Application\People::class,
            ],
            'process' => [
                Dvsa\Olcs\Api\Service\Publication\Process\Schedule41\Text1::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Application\Police::class
            ],
        ],
        'Schedule41UntruePublication' => [
            'context' => [
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceAddress::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Application\TransportManagers::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Application\People::class,
            ],
            'process' => [
                Dvsa\Olcs\Api\Service\Publication\Process\Application\Text1::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Variation\Text2::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Schedule41\Text3::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Application\Police::class
            ],
        ],
        'HearingPublication' => [
            'context' => [
                Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\PreviousHearingData::class,
                Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\Venue::class,
                Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\HearingDate::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Publication\PreviousPublicationNo::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceAddress::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\People::class
            ],
            'process' => [
                Dvsa\Olcs\Api\Service\Publication\Process\PiHearing\HearingText1::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Licence\Police::class
            ],
        ],
        'HearingDecision' => [
            'context' => [
                Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\PreviousHearingData::class,
                Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\Venue::class,
                Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\HearingDate::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Publication\PreviousPublicationNo::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceAddress::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\People::class
            ],
            'process' => [
                Dvsa\Olcs\Api\Service\Publication\Process\PiHearing\DecisionText1::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Licence\Police::class
            ],
        ],
        'TmHearingPublication' => [
            'context' => [
                Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\Venue::class,
                Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\HearingDate::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Publication\PreviousPublicationNo::class,
                Dvsa\Olcs\Api\Service\Publication\Context\TransportManager\TransportManagerName::class,
                Dvsa\Olcs\Api\Service\Publication\Context\TransportManager\Person::class
            ],
            'process' => [
                Dvsa\Olcs\Api\Service\Publication\Process\PiHearing\TmHearingText1::class,
                Dvsa\Olcs\Api\Service\Publication\Process\TransportManager\Police::class
            ],
        ],
        'TmHearingDecision' => [
            'context' => [
                Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\Venue::class,
                Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\HearingDate::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Publication\PreviousPublicationNo::class,
                Dvsa\Olcs\Api\Service\Publication\Context\TransportManager\TransportManagerName::class,
                Dvsa\Olcs\Api\Service\Publication\Context\TransportManager\Person::class
            ],
            'process' => [
                Dvsa\Olcs\Api\Service\Publication\Process\PiHearing\TmHearingText1::class,
                Dvsa\Olcs\Api\Service\Publication\Process\TransportManager\Police::class
            ],
        ],
        'BusGrantNew' => [
            'context' => [
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceAddress::class,
                Dvsa\Olcs\Api\Service\Publication\Context\BusReg\ServiceDesignation::class,
                Dvsa\Olcs\Api\Service\Publication\Context\BusReg\ServiceTypes::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\People::class
            ],
            'process' => [
                Dvsa\Olcs\Api\Service\Publication\Process\BusReg\Text2::class,
                Dvsa\Olcs\Api\Service\Publication\Process\BusReg\GrantNewText3::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Licence\Police::class
            ],
        ],
        'BusGrantVariation' => [
            'context' => [
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceAddress::class,
                Dvsa\Olcs\Api\Service\Publication\Context\BusReg\ServiceDesignation::class,
                Dvsa\Olcs\Api\Service\Publication\Context\BusReg\VariationReasons::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\People::class
            ],
            'process' => [
                Dvsa\Olcs\Api\Service\Publication\Process\BusReg\Text2::class,
                Dvsa\Olcs\Api\Service\Publication\Process\BusReg\GrantVarText3::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Licence\Police::class
            ],
        ],
        'BusGrantCancel' => [
            'context' => [
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceAddress::class,
                Dvsa\Olcs\Api\Service\Publication\Context\BusReg\ServiceDesignation::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\People::class
            ],
            'process' => [
                Dvsa\Olcs\Api\Service\Publication\Process\BusReg\Text2::class,
                Dvsa\Olcs\Api\Service\Publication\Process\BusReg\GrantCancelText3::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Licence\Police::class
            ],
        ],
        'ImpoundingLicencePublication' => [
            'context' => [
                Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\Venue::class,
                Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\HearingDate::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceNo::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\People::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceAddress::class,
            ],
            'process' => [
                Dvsa\Olcs\Api\Service\Publication\Process\Impounding\Text1::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Impounding\Text2::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Licence\Police::class
            ],
        ],
        'ImpoundingApplicationPublication' => [
            'context' => [
                Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\Venue::class,
                Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\HearingDate::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceNo::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Application\People::class,
                Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceAddress::class,
            ],
            'process' => [
                Dvsa\Olcs\Api\Service\Publication\Process\Impounding\Text1::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Impounding\Text2::class,
                Dvsa\Olcs\Api\Service\Publication\Process\Application\Police::class
            ],
        ],
    ],
    'submissions' => require(__DIR__ . '/submissions.config.php'),
    'ebsr' => [
        'transexchange_publisher' => [
            'templates' => [
                \Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClient::REQUEST_MAP_TEMPLATE =>
                    __DIR__ . '/../data/ebsr/requestmap_template.xml',
                \Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClient::TIMETABLE_TEMPLATE =>
                    __DIR__ . '/../data/ebsr/timetable_template.xml',
                \Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClient::DVSA_RECORD_TEMPLATE =>
                    __DIR__ . '/../data/ebsr/dvsarecord_template.xml'
            ]
        ],
    ],
    'xsd_mappings' => [
        'http://www.w3.org/2001/xml.xsd' => __DIR__ . '/../data/ebsr/xsd/xml.xsd',
        'http://www.transxchange.org.uk/schema/2.1/TransXChange_registration.xsd' =>
            __DIR__ . '/../data/ebsr/xsd/TransXChange_schema_2.1/TransXChange_registration.xsd',
        'http://www.transxchange.org.uk/schema/2.4/TransXChange_registration.xsd' =>
            __DIR__ . '/../data/ebsr/xsd/TransXChange_schema_2.4/TransXChange_registration.xsd',
        'http://www.transxchange.org.uk/schema/2.5/TransXChange_registration.xsd' =>
            __DIR__ . '/../data/ebsr/xsd/TransXChange_schema_2.5/TransXChange_registration.xsd',
        'http://naptan.dft.gov.uk/transxchange/publisher/schema/3.1.2/TransXChangePublisherService.xsd' =>
            __DIR__ . '/../data/ebsr/xsd/TransXChange_schema_2.4/TransXChangePublisherService_2_4.xsd',
        'https://webgate.ec.testa.eu/move-hub/erru/3.4' => __DIR__ . '/../data/nr/xsd/NotifyCheckResult_Request.xsd'
    ],
    'validators' => [
        'invokables' => [
            \Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\Operator::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\Operator::class,
            \Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\Registration::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\Registration::class,
            \Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\ServiceClassification::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\ServiceClassification::class,
            \Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\SupportingDocuments::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\SupportingDocuments::class,
            \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\EffectiveDate::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\EffectiveDate::class,
            \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\EndDate::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\EndDate::class,
            \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ApplicationType::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ApplicationType::class,
            \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\Licence::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\Licence::class,
            \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ServiceNo::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ServiceNo::class,
            \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\LocalAuthorityMissing::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\LocalAuthorityMissing::class,
            \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\NewAppAlreadyExists::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\NewAppAlreadyExists::class,
            \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\BusRegNotFound::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\BusRegNotFound::class,
            \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\RegisteredBusRoute::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\RegisteredBusRoute::class,
            \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\VariationNumber::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\VariationNumber::class,
            \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice\MissingSection::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice\MissingSection::class,
            \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice\MissingReason::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice\MissingReason::class,
            \Dvsa\Olcs\Api\Service\Nr\Validator\SiPenaltyImposedDate::class =>
                \Dvsa\Olcs\Api\Service\Nr\Validator\SiPenaltyImposedDate::class
        ],
        'aliases' => [
            'Rules\ProcessedData\LocalAuthorityMissing' =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\LocalAuthorityMissing::class,
            'Rules\ProcessedData\NewAppAlreadyExists' =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\NewAppAlreadyExists::class,
            'Rules\ProcessedData\BusRegNotFound' =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\BusRegNotFound::class,
            'Rules\ProcessedData\RegisteredBusRoute' =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\RegisteredBusRoute::class,
            'Rules\ProcessedData\VariationNumber' =>
                \Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\VariationNumber::class,
        ]
    ],
    'filters' => [
        'invokables' => [
            \Dvsa\Olcs\Api\Service\Ebsr\Filter\NoticePeriod::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\Filter\NoticePeriod::class,
            \Dvsa\Olcs\Api\Service\Ebsr\Filter\InjectIsTxcApp::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\Filter\InjectIsTxcApp::class,
            \Dvsa\Olcs\Api\Service\Ebsr\Filter\InjectNaptanCodes::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\Filter\InjectNaptanCodes::class,
            \Dvsa\Olcs\Api\Service\Ebsr\Filter\InjectReceivedDate::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\Filter\InjectReceivedDate::class,
            \Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\Subsidy::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\Subsidy::class,
            \Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\Via::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\Via::class,
            \Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\ExistingRegNo::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\ExistingRegNo::class,
            \Dvsa\Olcs\Api\Service\Nr\Filter\Format\IsExecuted::class =>
                \Dvsa\Olcs\Api\Service\Nr\Filter\Format\IsExecuted::class,
            \Dvsa\Olcs\Api\Service\Nr\Filter\Format\SiDates::class =>
                \Dvsa\Olcs\Api\Service\Nr\Filter\Format\SiDates::class,
            \Dvsa\Olcs\Api\Service\Nr\Filter\Format\MemberStateCode::class =>
                \Dvsa\Olcs\Api\Service\Nr\Filter\Format\MemberStateCode::class,
            \Dvsa\Olcs\Api\Service\Nr\Filter\LicenceNumber::class =>
                \Dvsa\Olcs\Api\Service\Nr\Filter\LicenceNumber::class,
            \Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\MiscSnJustification::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\MiscSnJustification::class,
            \Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\SubmissionResult::class =>
                \Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\SubmissionResult::class
        ],
        'factories' => [
            \Dvsa\Olcs\Api\Service\Nr\Filter\Vrm::class => \Dvsa\Olcs\Api\Service\Nr\Filter\VrmFactory::class
        ],
    ],
    'translator_plugins' => [
        'factories' => [
            Translator\TranslationLoader::class => Translator\TranslationLoaderFactory::class
        ],
    ],
    'translator' => [
        'locale' => [
            'en_GB', //default locale
            'en_GB', //fallback locale
        ],
        'remote_translation' => [
            [
                'type' => Translator\TranslationLoader::class,
            ]
        ],
    ],
];
