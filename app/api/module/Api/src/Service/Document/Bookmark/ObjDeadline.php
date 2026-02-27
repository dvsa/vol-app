<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * Obj deadline bookmark
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ObjDeadline extends PublicationFlatAbstract
{
    #[\Override]
    public function render()
    {
        $objectionDate = new \DateTime($this->data['pubDate']);
        $objectionDate->add(new \DateInterval('P21D'));

        return $objectionDate->format('d/m/Y');
    }
}
