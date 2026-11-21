<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Config;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * config/autoload/mocks.global-development.php, which points the API at the stand-ins for GOV.UK
 * One Login and the DVSA Address Service that compose-mocks.yaml starts.
 */
final class LocalMocksConfigTest extends TestCase
{
    private const string FILE = 'mocks.global-development.php';
    private const string SETTING = 'VOL_LOCAL_MOCKS';

    #[\Override]
    protected function tearDown(): void
    {
        putenv(self::SETTING);
    }

    private function config(?string $setting): array
    {
        putenv($setting === null ? self::SETTING : self::SETTING . '=' . $setting);

        return require dirname(__DIR__, 5) . '/config/autoload/' . self::FILE;
    }

    /**
     * The compose file is in the repository root, which isn't there when the tests run in a
     * container that only has app/api mounted.
     */
    private function composeFile(): string
    {
        $file = dirname(__DIR__, 7) . '/compose-mocks.yaml';

        if (!is_file($file)) {
            $this->markTestSkipped('compose-mocks.yaml is outside what this run can see');
        }

        return (string) file_get_contents($file);
    }

    #[DataProvider('settingsThatAreOff')]
    public function testItChangesNothingUnlessItIsSwitchedOn(?string $setting): void
    {
        $this->assertSame([], $this->config($setting));
    }

    public static function settingsThatAreOff(): array
    {
        return ['not set' => [null], 'empty' => [''], '0' => ['0'], 'false' => ['false']];
    }

    public function testSwitchedOnSignInGoesToTheMock(): void
    {
        $account = $this->config('1')['govuk_account'];
        $mock = 'http://mocksignin.local.olcs.dev-dvsacloud.uk';

        $this->assertSame($mock . '/.well-known/openid-configuration', $account['discovery_endpoint']);
        $this->assertSame($mock . '/.well-known/did.json', $account['core_identity_did_document_url']);
        $this->assertSame($mock, $account['expected_core_identity_issuer']);
    }

    public function testSwitchedOnAddressLookupsGoToTheMock(): void
    {
        $address = $this->config('1')['dvsa_address_service'];
        $mock = 'http://mockaddress.local.olcs.dev-dvsacloud.uk';

        $this->assertSame($mock, $address['client']['base_uri']);
        $this->assertStringStartsWith($mock . '/', $address['oauth2']['token_url']);
        $this->assertStringEndsWith('/oauth2/v2.0/token', $address['oauth2']['token_url']);
        // the mock turns away a token request without these, or with a scope that ends any other way
        $this->assertNotSame('', $address['oauth2']['client_id']);
        $this->assertNotSame('', $address['oauth2']['client_secret']);
        $this->assertStringEndsWith('/.default', $address['oauth2']['scope']);
    }

    public function testTheComposeFileStartsTheMockUnderBothNames(): void
    {
        $config = $this->config('1');
        $composeFile = $this->composeFile();
        $signIn = $config['govuk_account']['expected_core_identity_issuer'];
        $addressHost = parse_url($config['dvsa_address_service']['client']['base_uri'], PHP_URL_HOST);

        $this->assertStringContainsString('ISSUER_URL: ' . $signIn, $composeFile);
        $this->assertStringContainsString('ADDRESS_HOSTNAME: ' . $addressHost, $composeFile);
        $this->assertStringContainsString('- ' . parse_url($signIn, PHP_URL_HOST), $composeFile);
        $this->assertStringContainsString('- ' . $addressHost, $composeFile);
        $this->assertStringContainsString(self::SETTING . ': 1', $composeFile);
    }

    public function testItIsOnlyLoadedInDevelopment(): void
    {
        $this->assertMatchesRegularExpression('/\.global-development\.php$/', self::FILE);
    }
}
