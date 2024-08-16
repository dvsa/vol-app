<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\Licence;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Formatter;

/**
 * Class Licence Text2
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Text2 extends \Dvsa\Olcs\Api\Service\Publication\Process\AbstractText
{
    /**
     * @param PublicationLink $publicationLink
     * @param ImmutableArrayObject $context
     */
    public function process(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        $this->clear();

        if ($context->offsetExists('licenceCancelled')) {
            $this->addTextLine($context->offsetGet('licenceCancelled'));
        }

        $organisation = $publicationLink->getLicence()->getOrganisation();
        $this->addTextLine(Formatter\OrganisationName::format($organisation));
        $this->addTextLine(Formatter\People::format($organisation, $context->offsetGet('licencePeople')));

        $publicationLink->setText2($this->getTextWithNewLine());
    }
}
