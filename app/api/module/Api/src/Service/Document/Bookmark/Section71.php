<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * SECTION_7_1 bookmark
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Section71 extends AbstractPublicationLinkSection
{
    //section ids differ based on the publication type
    protected $pubTypeSection = [
        'A&D' => 10
    ];
}
