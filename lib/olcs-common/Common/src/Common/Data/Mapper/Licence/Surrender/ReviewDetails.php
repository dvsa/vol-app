<?php

namespace Common\Data\Mapper\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\Sections\LicenceDetails;
use Common\Data\Mapper\Licence\Surrender\Sections\SurrenderSection;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Mvc\Controller\Plugin\Url;

class ReviewDetails
{
    public static function makeSections(
        array $licence,
        Url $urlHelper,
        TranslationHelperService $translator,
        array $surrender
    ): array {
        $licenceDetails = new LicenceDetails($licence, $urlHelper, $translator);
        $discDetails = new SurrenderSection(
            $surrender,
            $urlHelper,
            $translator,
            SurrenderSection::DISC_SECTION
        );
        $discDetails->setHeading('licence.surrender.review.discs.heading');

        $operatorLicenceDetails = new SurrenderSection(
            $surrender,
            $urlHelper,
            $translator,
            SurrenderSection::OPERATORLICENCE_SECTION
        );
        $operatorLicenceDetails->setHeading('licence.surrender.review.documents.operatorlicence.heading');
        $operatorLicenceDetails->setDisplayChangeLinkInHeading(true);

        $sections = [
            $licenceDetails->makeSection(),
            $discDetails->makeSection(),
            $operatorLicenceDetails->makeSection(),

        ];

        if ($surrender['surrender']['isInternationalLicence']) {
            $communityLicenceDetails = new SurrenderSection(
                $surrender,
                $urlHelper,
                $translator,
                SurrenderSection::COMMUNITYLICENCE_SECTION
            );
            $communityLicenceDetails->setHeading('licence.surrender.review.documents.communitylicence.heading');
            $communityLicenceDetails->setDisplayChangeLinkInHeading(true);
            $sections[] = $communityLicenceDetails->makeSection();
        }

        return $sections;
    }
}
