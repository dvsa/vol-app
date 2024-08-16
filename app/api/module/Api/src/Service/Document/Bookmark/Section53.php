<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * SECTION_5_3 bookmark
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Section53 extends AbstractPublicationLinkSection
{
    //section ids differ based on the publication type
    protected $pubTypeSection = [
        'N&P' => 27,
        'A&D' => 27
    ];
}
