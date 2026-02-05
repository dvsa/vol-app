<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Stub;

use Dvsa\Olcs\Api\Service\Document\Bookmark\AbstractPublicationLinkSection;

class AbstractPublicationLinkSectionStub extends AbstractPublicationLinkSection
{
    public const TEST_PUB_TYPE_SECTION = 'unit_pubTypeSctn';

    public const TEST_SECTION_ID = 7777;

    protected $pubTypeSection = [
        self::TEST_PUB_TYPE_SECTION => [
            self::TEST_SECTION_ID,
            self::PUB_SECTION_18,
        ],
    ];
}
