<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark;

final class IdentityBundleBookmarkTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testStructure(mixed $bookmarkClass): void
    {
        $id = 1;
        $bundle = ['bundle'];

        $query = $bookmarkClass::create(
            [
                'id' => $id,
                'bundle' => $bundle
            ]
        );

        $this->assertSame($id, $query->getId());
        $this->assertSame($bundle, $query->getBundle());
    }

    public static function dataProvider(): \Iterator
    {
        yield [Bookmark\BusFeeTypeBundle::class];
        yield [Bookmark\BusRegBundle::class];
        yield [Bookmark\CaseBundle::class];
        yield [Bookmark\CommunityLicBundle::class];
        yield [Bookmark\DocParagraphBundle::class];
        yield [Bookmark\FeeBundle::class];
        yield [Bookmark\GoodsDiscBundle::class];
        yield [Bookmark\ImpoundingBundle::class];
        yield [Bookmark\IrfoGvPermitBundle::class];
        yield [Bookmark\IrhpApplicationBundle::class];
        yield [Bookmark\IrhpPermitBundle::class];
        yield [Bookmark\IrhpPermitStockBundle::class];
        yield [Bookmark\IrfoPsvAuthBundle::class];
        yield [Bookmark\LicenceBundle::class];
        yield [Bookmark\OppositionBundle::class];
        yield [Bookmark\OrganisationBundle::class];
        yield [Bookmark\PiHearingBundle::class];
        yield [Bookmark\PolicePeopleBundle::class];
        yield [Bookmark\PsvDiscBundle::class];
        yield [Bookmark\PublicationBundle::class];
        yield [Bookmark\StatementBundle::class];
        yield [Bookmark\TransportManagerBundle::class];
        yield [Bookmark\UserBundle::class];
        yield [Bookmark\VehicleBundle::class];
        yield [Bookmark\VenueBundle::class];
    }
}
