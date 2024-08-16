<?php

/**
 * AandDStoredPublicationDate bookmark
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * AandDStoredPublicationNumber bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class AandDStoredPublicationDate extends AbstractAandDStoredPublication
{
    /**
     * Render the bookmark
     *
     * @return string
     */
    public function render()
    {
        if (!isset($this->data['publicationLinks'])) {
            return '';
        }

        // unfortunately criteria doesn't work in this case, so we need to filter records manually
        $publicationLinks = $this->filterPublicationLinks($this->data['publicationLinks']);
        if (!count($publicationLinks)) {
            return '';
        }
        $publicationLinks = $this->sortPublicationLinks($publicationLinks);

        return (new DateTime($publicationLinks[0]['publication']['pubDate']))->format('d/m/Y');
    }
}
