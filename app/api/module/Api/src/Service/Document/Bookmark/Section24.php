<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * SECTION_2_4 bookmark
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Section24 extends AbstractPublicationLinkSection
{
    //section ids differ based on the publication type
    protected $pubTypeSection = [
        'N&P' => 8,
        'A&D' => 9
    ];
}
