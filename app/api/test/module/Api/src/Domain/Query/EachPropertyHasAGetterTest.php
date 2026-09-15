<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Query;

use Dvsa\Olcs\Api\Domain\Query;
use Dvsa\Olcs\Api\Domain\Query\Bookmark;

final class EachPropertyHasAGetterTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testEachPropertyHasGetter(mixed $bookmarkClass): void
    {
        $reflectionClass = new \ReflectionClass($bookmarkClass);

        foreach ($reflectionClass->getProperties() as $property) {
            $getMethod = 'get' . $property->getName();
            $this->assertTrue(method_exists($bookmarkClass, $getMethod));
        }
    }

    public static function dataProvider(): \Iterator
    {
        yield Bookmark\ApplicationBundle::class => [Bookmark\ApplicationBundle::class];
        yield Bookmark\ConditionsUndertakings::class => [Bookmark\ConditionsUndertakings::class];
        yield Bookmark\FStandingCapitalReserves::class => [Bookmark\FStandingCapitalReserves::class];
        yield Bookmark\InterimConditionsUndertakings::class => [Bookmark\InterimConditionsUndertakings::class];
        yield Bookmark\PreviousHearingBundle::class => [Bookmark\PreviousHearingBundle::class];
        yield Bookmark\PreviousPublicationByApplication::class => [Bookmark\PreviousPublicationByApplication::class];
        yield Bookmark\PreviousPublicationByLicence::class => [Bookmark\PreviousPublicationByLicence::class];
        yield Bookmark\PreviousPublicationByPi::class => [Bookmark\PreviousPublicationByPi::class];
        yield Bookmark\PublicationLatestByTaAndTypeBundle::class => [Bookmark\PublicationLatestByTaAndTypeBundle::class];
        yield Bookmark\PublicationLinkBundle::class => [Bookmark\PublicationLinkBundle::class];
        yield Bookmark\TotalContFee::class => [Bookmark\TotalContFee::class];
        yield Bookmark\Unpublished::class => [Bookmark\Unpublished::class];
        yield Bookmark\UnpublishedApplication::class => [Bookmark\UnpublishedApplication::class];
        yield Bookmark\UnpublishedBusReg::class => [Bookmark\UnpublishedBusReg::class];
        yield Bookmark\UnpublishedImpounding::class => [Bookmark\UnpublishedImpounding::class];
        yield Bookmark\UnpublishedLicence::class => [Bookmark\UnpublishedLicence::class];
        yield Bookmark\UnpublishedPi::class => [Bookmark\UnpublishedPi::class];
        yield Query\Application\NotTakenUpList::class => [Query\Application\NotTakenUpList::class];
        yield Query\Bus\ByLicenceRoute::class => [Query\Bus\ByLicenceRoute::class];
        yield Query\Bus\EbsrSubmissionList::class => [Query\Bus\EbsrSubmissionList::class];
        yield Query\Bus\PreviousVariationByRouteNo::class => [Query\Bus\PreviousVariationByRouteNo::class];
        yield Query\Bus\TxcInboxList::class => [Query\Bus\TxcInboxList::class];
        yield Query\User\UserListSelfserve::class => [Query\User\UserListSelfserve::class];
    }
}
