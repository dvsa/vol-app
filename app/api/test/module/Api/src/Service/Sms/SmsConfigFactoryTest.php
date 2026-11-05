<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Sms;

use Dvsa\Olcs\Api\Service\Sms\SmsConfig;
use Dvsa\Olcs\Api\Service\Sms\SmsConfigFactory;
use Dvsa\Olcs\Api\Service\Sms\SmsGate;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SmsConfigFactoryTest extends TestCase
{
    private function build(array $config): SmsConfig
    {
        $sm = new ServiceManager();
        $sm->setService('config', $config);

        return (new SmsConfigFactory())($sm, SmsConfig::class);
    }

    public function testLiveDsnYieldsApiKey(): void
    {
        $c = $this->build([
            'mail' => ['dsn' => 'govuknotify://KEY@default'],
            'email' => ['sms' => ['template_id' => 'tpl', 'send_all_sms_to' => '07700900000', 'is_production' => false]],
        ]);

        $this->assertSame('KEY', $c->apiKey);
        $this->assertFalse($c->mailpit);
        $this->assertSame('tpl', $c->templateId);
        $this->assertSame('+447700900000', $c->sendAllSmsTo);
        $this->assertFalse($c->isProduction);
    }

    public function testPasswordTakesPrecedenceOverUser(): void
    {
        $c = $this->build(['mail' => ['dsn' => 'govuknotify://user:PASS@default']]);
        $this->assertSame('PASS', $c->apiKey);
    }

    public function testMailpitDsn(): void
    {
        $c = $this->build(['mail' => ['dsn' => 'govuknotify+mailpit://mailpit:1026']]);

        $this->assertNull($c->apiKey);
        $this->assertTrue($c->mailpit);
        $this->assertSame('mailpit', $c->mailpitHost);
        $this->assertSame(1026, $c->mailpitPort);
    }

    #[DataProvider('offDsns')]
    public function testOtherDsnsAreOff(mixed $dsn): void
    {
        $c = $this->build(['mail' => ['dsn' => $dsn]]);

        $this->assertNull($c->apiKey);
        $this->assertFalse($c->mailpit);
        $this->assertFalse($c->hasNotifyTransport());
    }

    public static function offDsns(): array
    {
        return [
            'smtp' => ['smtp://host:25'],
            'empty' => [''],
            'blank' => [' '],
            'unresolved' => ['%olcs_mail_dsn%'],
            'missing' => [null],
        ];
    }

    #[DataProvider('unsetValues')]
    public function testSentinelsBecomeNull(string $value): void
    {
        $c = $this->build(['email' => ['sms' => ['template_id' => $value, 'send_all_sms_to' => $value, 'is_production' => true]]]);

        $this->assertNull($c->templateId);
        $this->assertNull($c->sendAllSmsTo);
        $this->assertTrue($c->isProduction);
    }

    public static function unsetValues(): array
    {
        return [[' '], [''], ['null'], [' NULL '], ['%olcs_notify_sms_template%']];
    }

    public function testProductionNullRedirect(): void
    {
        $c = $this->build(['email' => ['sms' => ['template_id' => 't', 'send_all_sms_to' => null, 'is_production' => true]]]);
        $this->assertNull($c->sendAllSmsTo);
    }

    public function testRedirectNumberIsNormalised(): void
    {
        $c = $this->build(['email' => ['sms' => ['send_all_sms_to' => '07700 900123']]]);
        $this->assertSame('+447700900123', $c->sendAllSmsTo);
    }

    public function testBadRedirectIsUnsetAndGateIsOff(): void
    {
        $c = $this->build([
            'mail' => ['dsn' => 'govuknotify://KEY@default'],
            'email' => ['sms' => ['template_id' => 'tpl', 'send_all_sms_to' => 'not-a-number', 'is_production' => false]],
        ]);

        $this->assertNull($c->sendAllSmsTo);

        $toggles = $this->createStub(ToggleService::class);
        $toggles->method('isEnabled')->willReturn(true);
        $this->assertFalse((new SmsGate($c, $toggles))->isEnabled());
    }
}
