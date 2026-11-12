<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Service;

use Dvsa\Olcs\Email\Service\NotifyConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NotifyConfig::class)]
final class NotifyConfigTest extends TestCase
{
    public function testReadsTheNotifyBlock(): void
    {
        $config = ['notify' => [
            'api_key' => ' key ',
            'proxy' => 'http://proxy:3128',
            'email' => [
                'passthrough_templates' => ['en_GB' => 'tpl-en', 'cy_GB' => 'tpl-cy'],
                'dev_chrome_template' => '<p>{{body}}</p>',
                'test_dsn' => 'govuknotify+mailpit://mailpit:1025',
            ],
            'sms' => ['template_id' => 'tpl-sms'],
        ]];

        $this->assertSame('key', NotifyConfig::apiKey($config));
        $this->assertSame('http://proxy:3128', NotifyConfig::proxy($config));
        $this->assertSame(['en_GB' => 'tpl-en', 'cy_GB' => 'tpl-cy'], NotifyConfig::passthroughTemplates($config));
        $this->assertSame('<p>{{body}}</p>', NotifyConfig::devChromeTemplate($config));
        $this->assertSame('tpl-sms', NotifyConfig::smsTemplateId($config));
        $this->assertSame('govuknotify+mailpit://mailpit:1025', NotifyConfig::testDsn($config));
    }

    public function testNothingConfigured(): void
    {
        $this->assertNull(NotifyConfig::apiKey([]));
        $this->assertNull(NotifyConfig::proxy([]));
        $this->assertSame([], NotifyConfig::passthroughTemplates([]));
        $this->assertNull(NotifyConfig::devChromeTemplate([]));
        $this->assertNull(NotifyConfig::smsTemplateId([]));
        $this->assertNull(NotifyConfig::testDsn([]));
    }

    /** Older local.php files set these under email.notify and email.sms. */
    public function testFallsBackToTheOldPlaces(): void
    {
        $config = [
            'email' => [
                'notify' => [
                    'proxy' => 'http://old-proxy:3128',
                    'passthrough_templates' => ['en_GB' => 'old-en', 'cy_GB' => 'old-cy'],
                    'dev_chrome_template' => 'old-chrome',
                ],
                'notify_test' => ['dsn' => 'govuknotify+mailpit://old:1025'],
                'sms' => ['template_id' => 'old-sms'],
            ],
            // what config.global.php gives a local run
            'notify' => [
                'api_key' => null,
                'proxy' => null,
                'email' => ['passthrough_templates' => ['en_GB' => null, 'cy_GB' => null], 'test_dsn' => null],
                'sms' => ['template_id' => null],
            ],
        ];

        $this->assertSame('http://old-proxy:3128', NotifyConfig::proxy($config));
        $this->assertSame(['en_GB' => 'old-en', 'cy_GB' => 'old-cy'], NotifyConfig::passthroughTemplates($config));
        $this->assertSame('old-chrome', NotifyConfig::devChromeTemplate($config));
        $this->assertSame('old-sms', NotifyConfig::smsTemplateId($config));
        $this->assertSame('govuknotify+mailpit://old:1025', NotifyConfig::testDsn($config));
    }

    public function testNewPlaceWinsWhenBothAreSet(): void
    {
        $config = [
            'email' => [
                'notify' => ['proxy' => 'http://old:1', 'passthrough_templates' => ['en_GB' => 'old-en', 'cy_GB' => 'old-cy']],
                'sms' => ['template_id' => 'old-sms'],
            ],
            'notify' => [
                'proxy' => 'http://new:1',
                'email' => ['passthrough_templates' => ['en_GB' => 'new-en', 'cy_GB' => ' ']],
                'sms' => ['template_id' => 'new-sms'],
            ],
        ];

        $this->assertSame('http://new:1', NotifyConfig::proxy($config));
        // per locale: the blank new Welsh id falls back to the old one
        $this->assertSame(['en_GB' => 'new-en', 'cy_GB' => 'old-cy'], NotifyConfig::passthroughTemplates($config));
        $this->assertSame('new-sms', NotifyConfig::smsTemplateId($config));
    }

    #[DataProvider('blankValues')]
    public function testBlankValuesAreUnset(mixed $value): void
    {
        $config = ['notify' => [
            'api_key' => $value,
            'proxy' => $value,
            'email' => ['passthrough_templates' => ['en_GB' => $value], 'test_dsn' => $value],
            'sms' => ['template_id' => $value],
        ]];

        $this->assertNull(NotifyConfig::apiKey($config));
        $this->assertNull(NotifyConfig::proxy($config));
        $this->assertSame([], NotifyConfig::passthroughTemplates($config));
        $this->assertNull(NotifyConfig::smsTemplateId($config));
        $this->assertNull(NotifyConfig::testDsn($config));
    }

    public static function blankValues(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'space' => [' '],
            'null sentinel' => ['NULL'],
            'unresolved placeholder' => ['%olcs_notify_api_key%'],
            'not a string' => [123],
        ];
    }
}
