<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\Licence;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;

/**
 * Class Text3
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Text3 extends \Dvsa\Olcs\Api\Service\Publication\Process\AbstractText
{
    public function process(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        $this->clear();

        $this->addTextLine($context->offsetGet('licenceAddress'));
        if ($context->offsetExists('busNote')) {
            $this->addTextLine($context->offsetGet('busNote'));
        }

        $publicationLink->setText3($this->getTextWithNewLine());
    }
}
