<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Rbac\Service\Permission;
use Common\Service\Helper\UrlHelperService as UrlHelper;
use Common\Service\Table\Formatter\InternalLicencePermitReference;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class InternalLicencePermitReferenceTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $permissionService;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelper::class);
        $this->permissionService = m::mock(Permission::class);

        $this->sut = new InternalLicencePermitReference($this->urlHelper, $this->permissionService);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    public function testFormat(): void
    {
        $appId = 4;
        $licenceId = 200;
        $expectedOutput = '<a class="govuk-link" href="INTERNAL_IRHP_URL">OB1234567/4&gt;</a>'; //escaped as proved by &gt;

        $row = [
            'id' => $appId,
            'licenceId' => $licenceId,
            'applicationRef' => 'OB1234567/4>',
        ];

        $routeParams = [
            'action' => 'edit',
            'irhpAppId' => $appId,
            'licence' => $licenceId
        ];

        $this->urlHelper->shouldReceive('fromRoute')
            ->with('licence/irhp-application/application', $routeParams)
            ->once()
            ->andReturn('INTERNAL_IRHP_URL');

        $this->permissionService->expects('isInternalReadOnly')->withNoArgs()->andReturnFalse();

        $this->assertEquals(
            $expectedOutput,
            $this->sut->format($row)
        );
    }

    public function testFormatInternalReadOnly(): void
    {
        $expectedOutput = 'OB1234567/4&gt;'; //escaped as proved by &gt;

        $row = [
            'applicationRef' => 'OB1234567/4>',
        ];

        $this->permissionService->expects('isInternalReadOnly')->withNoArgs()->andReturnTrue();

        $this->assertEquals(
            $expectedOutput,
            $this->sut->format($row)
        );
    }
}
