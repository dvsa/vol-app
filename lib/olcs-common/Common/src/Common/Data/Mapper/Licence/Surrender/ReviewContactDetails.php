<?php

namespace Common\Data\Mapper\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\Sections\ContactDetails;
use Common\Data\Mapper\Licence\Surrender\Sections\CorrespondenceAddress;
use Common\Data\Mapper\Licence\Surrender\Sections\LicenceDetails;
use Common\Service\Helper\TranslationHelperService;

class ReviewContactDetails
{
    public static function makeSections(
        array $licence,
        \Laminas\Mvc\Controller\Plugin\Url $urlHelper,
        TranslationHelperService $translator
    ): array {
        $licenceDetails = new LicenceDetails($licence, $urlHelper, $translator);
        $correspondenceAddress = new CorrespondenceAddress($licence, $urlHelper, $translator);
        $contactDetails = new ContactDetails($licence, $urlHelper, $translator);

        return [
            $licenceDetails->makeSection(),
            $correspondenceAddress->makeSection(),
            $contactDetails->makeSection()
        ];
    }
}
