<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService as UrlHelper;
use Common\Service\Table\Formatter\LicenceApplication;
use Common\View\Helper\Status as StatusHelper;
use Laminas\View\HelperPluginManager as ViewPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Licence and application test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceApplicationTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $viewHelperManager;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelper::class);
        $this->viewHelperManager = m::mock(ViewPluginManager::class);
        $this->sut = new LicenceApplication($this->urlHelper, $this->viewHelperManager);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @dataProvider dpTestFormat
     *
     * @param $expected
     * @param $appTimes
     * @param $extraRowData
     */
    public function testFormat($expected, $extraRowData, $appTimes): void
    {
        $licStatus = 'lic status';
        $licStatusDesc = 'lic status desc';
        $formattedLicStatus = 'formatted lic status';
        $licUrl = 'http://licURL';
        $licNo = 'OB1234567';
        $licId = 1234;

        $licStatusArray = [
            'id' => $licStatus,
            'description' => $licStatusDesc
        ];

        $appStatus = 'app status';
        $appStatusDesc = 'app status desc';
        $formattedAppStatus = 'formatted app status';
        $appUrl = 'http://appURL';
        $appId = 5678;

        $appStatusArray = [
            'id' => $appStatus,
            'description' => $appStatusDesc
        ];

        $row = [
            'licId' => $licId,
            'licNo' => $licNo,
            'licStatus' => $licStatus,
            'licStatusDesc' => $licStatusDesc,
            'appStatus' => $appStatus,
            'appStatusDesc' => $appStatusDesc
        ];

        $row += $extraRowData;

        $statusService = m::mock(StatusHelper::class);
        $statusService->shouldReceive('__invoke')->with($licStatusArray)->once()->andReturn($formattedLicStatus);
        $statusService->shouldReceive('__invoke')
            ->with($appStatusArray)
            ->times($appTimes)
            ->andReturn($formattedAppStatus);

        $this->urlHelper->shouldReceive('fromRoute')
            ->with('licence', ['licence' => $licId])
            ->once()
            ->andReturn($licUrl);
        $this->urlHelper->shouldReceive('fromRoute')
            ->with('lva-application', ['application' => $appId])
            ->times($appTimes)
            ->andReturn($appUrl);

        $this->viewHelperManager->shouldReceive('get')->with('status')->once()->andReturn($statusService);

        $this->assertEquals($expected, $this->sut->format($row, []));
    }

    /**
     * data provider for testLicenceApplicationFormatter
     *
     * @return array
     */
    public function dpTestFormat()
    {
        $licenceLink = '<a class="govuk-link" href="http://licURL">OB1234567</a>formatted lic status';
        $appLink = '<a class="govuk-link" href="http://appURL">5678</a>formatted app status';

        return [
            [$licenceLink . '<br />' . $appLink, ['appId' => 5678], 1],
            [$licenceLink, [], 0],
        ];
    }
}
