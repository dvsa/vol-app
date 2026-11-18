<?php

declare(strict_types=1);

namespace AdminTest\Controller;

use Admin\Controller\SmsTemplateController;
use Admin\Controller\TemplateController;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Navigation\Navigation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class SmsTemplateControllerTest extends MockeryTestCase
{
    private SmsTemplateController&m\MockInterface $sut;

    private Params&m\MockInterface $params;

    public function setUp(): void
    {
        $this->params = m::mock(Params::class);

        $this->sut = m::mock(
            SmsTemplateController::class,
            [
                m::mock(TranslationHelperService::class),
                m::mock(FormHelperService::class),
                m::mock(FlashMessengerHelperService::class),
                m::mock(Navigation::class),
            ]
        )->makePartial()->shouldAllowMockingProtectedMethods();

        $this->sut->shouldReceive('params')->andReturn($this->params);
    }

    public function testIsATemplateController(): void
    {
        $this->assertInstanceOf(TemplateController::class, $this->sut);
    }

    public function testListForcesSmsFormat(): void
    {
        // A format in the query string must not be able to widen the list beyond SMS.
        $this->params->shouldReceive('fromQuery')->with('emailTemplateCategory')->andReturn(null);
        $this->params->shouldReceive('fromQuery')->with('format', '')->andReturn('html');
        $this->params->shouldReceive('fromQuery')->with('format')->andReturn('html');

        $result = $this->sut->modifyListQueryParameters([]);

        $this->assertSame('sms', $result['format']);
    }

    public function testNoFilterFormAndSmsRoute(): void
    {
        $props = new \ReflectionClass(SmsTemplateController::class);
        $defaults = $props->getDefaultProperties();

        $this->assertSame('', $defaults['filterForm']);
        $this->assertSame('admin-dashboard/admin-sms-templates', $defaults['templateRoute']);
        $this->assertSame('admin-sms-templates', $defaults['tableName']);
        $this->assertSame('admin-dashboard/content-management', $defaults['navigationId']);
    }
}
