<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\Impounding;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\AbstractText;

/**
 * Class Impounding Text3
 *
 * @author Teja Vaddala <teja.vaddala@dvsa.gov.uk>
 */
final class Text3 extends AbstractText
{
    /**
     * @param PublicationLink $publicationLink
     * @param ImmutableArrayObject $context
     * @return PublicationLink
     */
    #[\Override]
    public function process(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        $this->addTextLine($context->offsetGet('outcome'));

        $publicationLink->setText3($this->getTextWithNewLine());

        return $publicationLink;
    }
}
