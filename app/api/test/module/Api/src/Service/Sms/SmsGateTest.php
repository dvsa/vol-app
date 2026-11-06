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
    public function testIsEnabled(?string $apiKey, bool $mailpit, ?string $template, ?string $redirect, bool $prod, bool $toggle, bool $expected, bool $redirectInvalid = false): void
    {
        $toggles = $this->createStub(ToggleService::class);
        $toggles->method('isEnabled')->willReturn($toggle);

        $config = new SmsConfig($apiKey, $mailpit, $mailpit ? 'mailpit' : null, 1025, $template, $redirect, $prod, 'dev', $redirectInvalid);

        $this->assertSame($expected, (new SmsGate($config, $toggles))->isEnabled());
    }

    public static function cases(): array
    {
        return [
            'smtp dsn' => [null, false, 'tpl', '07700900000', false, true, false],
            'blank template' => ['KEY', false, null, '07700900000', false, true, false],
            'toggle off' => ['KEY', false, 'tpl', '07700900000', false, false, false],
            // relies on a team or test key to keep real numbers out
            'non-prod without redirect' => ['KEY', false, 'tpl', null, false, true, true],
            'non-prod with a redirect that is not a number' => ['KEY', false, 'tpl', null, false, true, false, true],
            'non-prod with number' => ['KEY', false, 'tpl', '07700900000', false, true, true],
            'non-prod mailpit with number' => [null, true, 'tpl', '07700900000', false, true, true],
            'prod no redirect' => ['KEY', false, 'tpl', null, true, true, true],
            'prod toggle off' => ['KEY', false, 'tpl', null, true, false, false],
        ];
    }
}
