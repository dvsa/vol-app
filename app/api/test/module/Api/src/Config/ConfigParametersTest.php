<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Config;

use Dvsa\LaminasConfigCloudParameters\Exception\ParameterNotFoundException;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Loads the app's modules the way it boots, so dvsa/laminas-config-cloud-parameters' merge listener runs over the
 * real merged config. Nothing else in the suite runs it, which left upgrades of that library and the Symfony
 * components it resolves placeholders with unguarded.
 *
 * Only *.global.php is read, so a developer's local.php can't change the outcome, and whatever providers the config
 * names are swapped for a stub before the listener runs, so nothing reaches AWS. Separate processes because loading
 * modules can set static state that would leak into other tests.
 */
#[RunTestsInSeparateProcesses]
class ConfigParametersTest extends TestCase
{
    /**
     * Every placeholder the config expects Parameter Store / Secrets Manager to supply. When this changes, the
     * parameter has to be added to (or can be removed from) every environment too.
     */
    private const array EXPECTED_PARAMETERS = [
        'address_service_azure_client_id',
        'address_service_azure_client_secret',
        'address_service_azure_token_scope',
        'address_service_azure_token_url',
        'address_service_url',
        'aws_cognito_client_id',
        'aws_cognito_client_secret',
        'aws_cognito_pool_id',
        'aws_cognito_region',
        'cache_encryption_secret_api',
        'cache_encryption_secret_shared',
        'companies_house_api_base_uri',
        'company_house_dlq_notification_email_address',
        'data-dva-ni-export-s3uri',
        'data-gov-uk-export-s3uri',
        'domain',
        'dvsa_reports_api_key',
        'erru_version',
        'govuk_account_client_id',
        'govuk_account_core_identity_did_document_url',
        'govuk_account_discovery_endpoint',
        'govuk_account_id_assurance_issuer',
        'govuk_account_private_key',
        'govuk_account_private_key_algorithm',
        'govuk_account_public_key',
        'idp_dedupe_success_window_hours',
        'idp_sweeper_threshold_minutes',
        'lar_base_uri',
        'lar_vol_ref_lookup_api_key',
        'log_level',
        'olcs_api_opendj_password',
        'olcs_api_rds_password',
        'olcs_aws_account_number',
        'olcs_aws_region',
        'olcs_aws_s3_role_arn',
        'olcs_aws_s3_role_session_name',
        'olcs_aws_sqs_base_uri',
        'olcs_aws_sqs_ch_get_dlq',
        'olcs_aws_sqs_ch_get_queue',
        'olcs_aws_sqs_ch_insolvency_dlq',
        'olcs_aws_sqs_ch_insolvency_queue',
        'olcs_aws_version',
        'olcs_companieshouseapikey',
        'olcs_companieshousexmlpassword',
        'olcs_companieshousexmluserid',
        'olcs_cpms_gateway_client_id',
        'olcs_cpms_gateway_client_secret',
        'olcs_cpms_gateway_host',
        'olcs_cpms_gateway_scope',
        'olcs_cpms_gateway_token_url',
        'olcs_cpmsclientid',
        'olcs_cpmsclientid_ni',
        'olcs_cpmssecret',
        'olcs_cpmssecret_ni',
        'olcs_cpmsserver',
        'olcs_doctrine_encryption_key',
        'olcs_document_store_backend',
        'olcs_document_store_s3_bucket',
        'olcs_document_store_s3_key_prefix',
        'olcs_dvla_search_api_key',
        'olcs_dvla_search_base_uri',
        'olcs_email_host',
        'olcs_email_port',
        'olcs_from_email',
        'olcs_imap_host',
        'olcs_imap_password',
        'olcs_imap_port',
        'olcs_imap_ssl',
        'olcs_imap_user',
        'olcs_iu_uri',
        'olcs_mail_dsn',
        'olcs_natreg_client_id',
        'olcs_natreg_client_scope',
        'olcs_natreg_client_secret',
        'olcs_natreg_token_url',
        'olcs_natreg_uri',
        'olcs_notify_template_cy_gb',
        'olcs_notify_template_en_gb',
        'olcs_notify_test_dsn',
        'olcs_retrieval_session_secret',
        'olcs_send_all_mail_to',
        'olcs_ss_uri',
        'olcs_txc_client_id',
        'olcs_txc_client_secret',
        'olcs_txc_scope',
        'olcs_txc_token_url',
        'olcs_webdav',
        'operator_reports_api_url',
        'redis_cache_fqdn',
        'shd_proxy',
        'transxchange_aws_consumer_role',
        'transxchange_aws_s3_input_bucket',
        'transxchange_aws_s3_output_bucket',
        'transxchange_aws_sqs_output_uri',
        'transxchange_uri',
    ];

    public function testConfigReferencesExactlyTheExpectedParameters(): void
    {
        StubParameterProvider::$parameters = [];

        try {
            $this->loadConfig();
            $names = [];
        } catch (ParameterNotFoundException $e) {
            $names = $e->getUnresolvedParameterNames();
        }

        sort($names);

        $this->assertSame(
            self::EXPECTED_PARAMETERS,
            $names,
            'The placeholders in the config have changed: update EXPECTED_PARAMETERS, and every environment\'s parameters'
        );
    }

    public function testMergeListenerResolvesEveryPlaceholder(): void
    {
        StubParameterProvider::$parameters = self::EXPECTED_PARAMETERS;

        $config = $this->loadConfig();

        $json = json_encode($config, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $this->assertIsString($json);
        $this->assertDoesNotMatchRegularExpression('/%[\w.-]+%/', $json);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadConfig(): array
    {
        $configDir = dirname(__DIR__, 5) . '/config';

        $appConfig = require $configDir . '/application.config.php';
        $appConfig['module_listener_options'] = array_merge($appConfig['module_listener_options'], [
            'config_glob_paths' => [$configDir . '/autoload/{,*.}global.php'],
            'config_cache_enabled' => false,
            'module_map_cache_enabled' => false,
        ]);

        $serviceManager = new ServiceManager();
        (new ServiceManagerConfig($appConfig['service_manager'] ?? []))->configureServiceManager($serviceManager);
        $serviceManager->setService('ApplicationConfig', $appConfig);

        $moduleManager = $serviceManager->get('ModuleManager');
        assert($moduleManager instanceof ModuleManager);

        // After the config files are merged (priority 1000), before the config-parameters listener runs (priority 1)
        $moduleManager->getEventManager()->attach(ModuleEvent::EVENT_MERGE_CONFIG, function (ModuleEvent $e): void {
            $configListener = $e->getConfigListener();
            assert($configListener !== null);
            $config = $configListener->getMergedConfig(false);
            $config['config_parameters']['providers'] = [StubParameterProvider::class => ['stub']];
            $configListener->setMergedConfig($config);
        }, 500);

        $moduleManager->loadModules();

        $config = $serviceManager->get('config');
        assert(is_array($config));

        return $config;
    }
}
