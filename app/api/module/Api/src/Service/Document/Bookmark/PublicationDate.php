<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * Publication date bookmark
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PublicationDate extends PublicationFlatAbstract
{
    public const FORMATTER = 'Date';
    public const FIELD  = 'pubDate';
}
