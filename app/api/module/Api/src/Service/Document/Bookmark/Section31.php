<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * SECTION_3_1 bookmark
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Section31 extends AbstractPublicationLinkSection
{
    //section ids differ based on the publication type
    protected $pubTypeSection = [
        'N&P' => 21,
        'A&D' => 16
    ];
}
