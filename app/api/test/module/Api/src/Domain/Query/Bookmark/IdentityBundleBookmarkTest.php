<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark;

class IdentityBundleBookmarkTest extends \PHPUnit\Framework\TestCase
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

    public static function dataProvider(): array
    {
        return [
            [Bookmark\BusFeeTypeBundle::class],
            [Bookmark\BusRegBundle::class],
            [Bookmark\CaseBundle::class],
            [Bookmark\CommunityLicBundle::class],
            [Bookmark\DocParagraphBundle::class],
            [Bookmark\FeeBundle::class],
            [Bookmark\GoodsDiscBundle::class],
            [Bookmark\ImpoundingBundle::class],
            [Bookmark\IrfoGvPermitBundle::class],
            [Bookmark\IrhpApplicationBundle::class],
            [Bookmark\IrhpPermitBundle::class],
            [Bookmark\IrhpPermitStockBundle::class],
            [Bookmark\IrfoPsvAuthBundle::class],
            [Bookmark\LicenceBundle::class],
            [Bookmark\OppositionBundle::class],
            [Bookmark\OrganisationBundle::class],
            [Bookmark\PiHearingBundle::class],
            [Bookmark\PolicePeopleBundle::class],
            [Bookmark\PsvDiscBundle::class],
            [Bookmark\PublicationBundle::class],
            [Bookmark\StatementBundle::class],
            [Bookmark\TransportManagerBundle::class],
            [Bookmark\UserBundle::class],
            [Bookmark\VehicleBundle::class],
            [Bookmark\VenueBundle::class],
        ];
    }
}
