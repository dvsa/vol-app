<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Query;

use Dvsa\Olcs\Api\Domain\Query;
use Dvsa\Olcs\Api\Domain\Query\Bookmark;

class EachPropertyHasAGetterTest extends \PHPUnit\Framework\TestCase
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

    public static function dataProvider(): array
    {
        return [
            Bookmark\ApplicationBundle::class => [Bookmark\ApplicationBundle::class],
            Bookmark\ConditionsUndertakings::class => [Bookmark\ConditionsUndertakings::class],
            Bookmark\FStandingCapitalReserves::class => [Bookmark\FStandingCapitalReserves::class],
            Bookmark\InterimConditionsUndertakings::class => [Bookmark\InterimConditionsUndertakings::class],
            Bookmark\PreviousHearingBundle::class => [Bookmark\PreviousHearingBundle::class],
            Bookmark\PreviousPublicationByApplication::class => [Bookmark\PreviousPublicationByApplication::class],
            Bookmark\PreviousPublicationByLicence::class => [Bookmark\PreviousPublicationByLicence::class],
            Bookmark\PreviousPublicationByPi::class => [Bookmark\PreviousPublicationByPi::class],
            Bookmark\PublicationLatestByTaAndTypeBundle::class => [Bookmark\PublicationLatestByTaAndTypeBundle::class],
            Bookmark\PublicationLinkBundle::class => [Bookmark\PublicationLinkBundle::class],
            Bookmark\TotalContFee::class => [Bookmark\TotalContFee::class],
            Bookmark\Unpublished::class => [Bookmark\Unpublished::class],
            Bookmark\UnpublishedApplication::class => [Bookmark\UnpublishedApplication::class],
            Bookmark\UnpublishedBusReg::class => [Bookmark\UnpublishedBusReg::class],
            Bookmark\UnpublishedImpounding::class => [Bookmark\UnpublishedImpounding::class],
            Bookmark\UnpublishedLicence::class => [Bookmark\UnpublishedLicence::class],
            Bookmark\UnpublishedPi::class => [Bookmark\UnpublishedPi::class],

            Query\Application\NotTakenUpList::class => [Query\Application\NotTakenUpList::class],
            Query\Bus\ByLicenceRoute::class => [Query\Bus\ByLicenceRoute::class],
            Query\Bus\EbsrSubmissionList::class => [Query\Bus\EbsrSubmissionList::class],
            Query\Bus\PreviousVariationByRouteNo::class => [Query\Bus\PreviousVariationByRouteNo::class],
            Query\Bus\TxcInboxList::class => [Query\Bus\TxcInboxList::class],

            Query\User\UserListSelfserve::class => [Query\User\UserListSelfserve::class],
        ];
    }
}
