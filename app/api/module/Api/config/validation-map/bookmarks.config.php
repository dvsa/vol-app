<?php

/**
 * @NOTE This is the home of all bookmark queries, bookmarks queries are called during doc generation, so it the
 * user has access to create the doc, then they need access to the bookmark
 */

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;

return [
    QueryHandler\Bookmark\ApplicationBundle::class                  => NoValidationRequired::class,
    QueryHandler\Bookmark\BusRegBundle::class                       => NoValidationRequired::class,
    QueryHandler\Bookmark\BusFeeTypeBundle::class                   => NoValidationRequired::class,
    QueryHandler\Bookmark\CommunityLicBundle::class                 => NoValidationRequired::class,
    QueryHandler\Bookmark\ConditionsUndertakings::class             => NoValidationRequired::class,
    QueryHandler\Bookmark\DocParagraphBundle::class                 => NoValidationRequired::class,
    QueryHandler\Bookmark\FStandingCapitalReserves::class           => NoValidationRequired::class,
    QueryHandler\Bookmark\FeeBundle::class                          => NoValidationRequired::class,
    QueryHandler\Bookmark\GoodsDiscBundle::class                    => NoValidationRequired::class,
    QueryHandler\Bookmark\ImpoundingBundle::class                   => NoValidationRequired::class,
    QueryHandler\Bookmark\InterimConditionsUndertakings::class      => NoValidationRequired::class,
    QueryHandler\Bookmark\InterimOperatingCentres::class            => NoValidationRequired::class,
    QueryHandler\Bookmark\InterimUnlinkedTm::class                  => NoValidationRequired::class,
    QueryHandler\Bookmark\IrfoGvPermitBundle::class                 => NoValidationRequired::class,
    QueryHandler\Bookmark\IrhpApplicationBundle::class              => NoValidationRequired::class,
    QueryHandler\Bookmark\IrhpPermitBundle::class                   => NoValidationRequired::class,
    QueryHandler\Bookmark\IrhpPermitStockBundle::class              => NoValidationRequired::class,
    QueryHandler\Bookmark\IrfoPsvAuthBundle::class                  => NoValidationRequired::class,
    QueryHandler\Bookmark\LicenceBundle::class                      => NoValidationRequired::class,
    QueryHandler\Bookmark\LicencePsvDiscCountNotCeased::class       => NoValidationRequired::class,
    QueryHandler\Bookmark\OppositionBundle::class                   => NoValidationRequired::class,
    QueryHandler\Bookmark\OrganisationBundle::class                 => NoValidationRequired::class,
    QueryHandler\Bookmark\PiHearingBundle::class                    => NoValidationRequired::class,
    QueryHandler\Bookmark\PreviousHearing::class                    => NoValidationRequired::class,
    QueryHandler\Bookmark\PreviousPublication::class                => NoValidationRequired::class,
    QueryHandler\Bookmark\PsvDiscBundle::class                      => NoValidationRequired::class,
    QueryHandler\Bookmark\PublicationBundle::class                  => NoValidationRequired::class,
    QueryHandler\Bookmark\PublicationLatestByTaAndTypeBundle::class => NoValidationRequired::class,
    QueryHandler\Bookmark\PublicationLinkBundle::class              => NoValidationRequired::class,
    QueryHandler\Bookmark\PolicePeople::class                       => NoValidationRequired::class,
    QueryHandler\Bookmark\StatementBundle::class                    => NoValidationRequired::class,
    QueryHandler\Bookmark\TotalContFee::class                       => NoValidationRequired::class,
    QueryHandler\Bookmark\TransportManagerBundle::class             => NoValidationRequired::class,
    QueryHandler\Bookmark\UserBundle::class                         => NoValidationRequired::class,
    QueryHandler\Bookmark\VehicleBundle::class                      => NoValidationRequired::class,
    QueryHandler\Bookmark\VenueBundle::class                        => NoValidationRequired::class,
    QueryHandler\Bookmark\HearingBundle::class                      => NoValidationRequired::class,
    QueryHandler\Bookmark\CaseBundle::class                         => NoValidationRequired::class,
    QueryHandler\Bookmark\CompaniesHouseCompanyBundle::class        => NoValidationRequired::class
];
