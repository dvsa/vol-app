<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepo;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Api\Service\Document\Bookmark\GvDiscMargins;
use Dvsa\Olcs\Api\Service\Document\Bookmark\PsvDiscMargins;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Gv/Psv disc page-margin bookmarks
 */
class DiscMarginsTest extends MockeryTestCase
{
    public function testGvRendersSystemParameterValues(): void
    {
        $bookmark = new GvDiscMargins();
        $bookmark->setRepoManager($this->repoManagerWith([
            SystemParameter::GOODS_DISC_MARGIN_TOP => '1200',
            SystemParameter::GOODS_DISC_MARGIN_LEFT => '1500',
        ]));

        $this->assertTrue($bookmark->isStatic());
        $this->assertTrue($bookmark->isPreformatted());
        $this->assertSame('\margt1200\margl1500', $bookmark->render());
    }

    public function testGvFallsBackToCalibratedDefaults(): void
    {
        $bookmark = new GvDiscMargins();
        $bookmark->setRepoManager($this->repoManagerWith([
            SystemParameter::GOODS_DISC_MARGIN_TOP => null,
            SystemParameter::GOODS_DISC_MARGIN_LEFT => 'not-a-number',
        ]));

        $this->assertSame('\margt1094\margl1607', $bookmark->render());
    }

    public function testGvFallsBackWithoutRepoManager(): void
    {
        $this->assertSame('\margt1094\margl1607', (new GvDiscMargins())->render());
    }

    public function testPsvRendersSystemParameterValues(): void
    {
        $bookmark = new PsvDiscMargins();
        $bookmark->setRepoManager($this->repoManagerWith([
            SystemParameter::PSV_DISC_MARGIN_TOP => '1000',
            SystemParameter::PSV_DISC_MARGIN_LEFT => '1700',
        ]));

        $this->assertSame('\margt1000\margl1700', $bookmark->render());
    }

    public function testPsvFallsBackToCalibratedDefaults(): void
    {
        $this->assertSame('\margt1128\margl1633', (new PsvDiscMargins())->render());
    }

    private function repoManagerWith(array $values): RepositoryServiceManager
    {
        $repo = m::mock(SystemParameterRepo::class);
        $repo->shouldReceive('fetchNumericValue')
            ->andReturnUsing(static function (string $param, int $default) use ($values): int {
                $value = $values[$param] ?? null;
                return is_numeric($value) ? (int)$value : $default;
            });

        $repoManager = m::mock(RepositoryServiceManager::class);
        $repoManager->shouldReceive('get')->with('SystemParameter')->andReturn($repo);

        return $repoManager;
    }
}
