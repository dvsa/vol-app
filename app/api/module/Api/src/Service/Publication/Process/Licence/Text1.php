<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\Licence;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;

/**
 * Class Licence Text1
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Text1 extends \Dvsa\Olcs\Api\Service\Publication\Process\AbstractText
{
    /**
     * @param PublicationLink $publicationLink
     * @param ImmutableArrayObject $context
     */
    public function process(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        $this->clear();

        $this->addTextLine(
            $publicationLink->getLicence()->getLicNo() . ' ' . $publicationLink->getLicence()->getLicenceTypeShortCode()
        );

        if (
            $publicationLink->getLicence()->isGoods() &&
            $context->offsetExists('previousPublication')
        ) {
            $this->addTextLine(sprintf('(%s)', $context->offsetGet('previousPublication')));
        }

        $publicationLink->setText1($this->getTextWithNewLine());
    }
}
