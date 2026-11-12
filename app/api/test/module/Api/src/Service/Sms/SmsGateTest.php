<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Sms;

use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Service\Sms\SmsConfig;
use Dvsa\Olcs\Api\Service\Sms\SmsGate;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SmsGateTest extends TestCase
{
    #[DataProvider('cases')]
    public function testIsEnabled(?string $apiKey, bool $mailpit, ?string $template, bool $toggle, bool $expected): void
    {
        $toggles = $this->createStub(ToggleService::class);
        $toggles->method('isEnabled')->willReturn($toggle);

        $config = new SmsConfig($apiKey, $mailpit, $mailpit ? 'mailpit' : null, 1025, $template, 'dev');

        $this->assertSame($expected, (new SmsGate($config, $toggles))->isEnabled());
    }

    public static function cases(): array
    {
        return [
            'smtp dsn' => [null, false, 'tpl', true, false],
            'blank template' => ['KEY', false, null, true, false],
            'toggle off' => ['KEY', false, 'tpl', false, false],
            'notify key, template and toggle' => ['KEY', false, 'tpl', true, true],
            'mailpit, template and toggle' => [null, true, 'tpl', true, true],
        ];
    }
}
