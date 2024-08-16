<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\Impounding;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\AbstractText;
use Dvsa\Olcs\Api\Service\Publication\Formatter;

/**
 * Class Impounding Text1
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class Text1 extends AbstractText
{
    protected $text1 = 'Impounding hearing (%s) to be held at %s, on %s commencing at %s';

    /**
     * @param PublicationLink $publication
     * @param ImmutableArrayObject $context
     * @return PublicationLink
     */
    public function process(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        $this->addTextLine(
            $this->getImpoundingDetailsText($publicationLink, $context)
        );
        $this->addTextLine($context->offsetGet('licenceNo'));

        $this->getOrganisationOfficersText($publicationLink, $context);

        $this->addTextLine($context->offsetGet('licenceAddress'));

        $publicationLink->setText1($this->getTextWithNewLine());

        return $publicationLink;
    }

    /**
     * @param PublicationLink $publication
     * @return String
     */
    private function getImpoundingDetailsText(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        return sprintf(
            $this->text1,
            $publicationLink->getImpounding()->getId(),
            $context->offsetGet('venueOther'),
            $context->offsetGet('formattedHearingDate'),
            $context->offsetGet('formattedHearingTime')
        );
    }

    /**
     * @param PublicationLink $publication
     * @return string|void
     */
    private function getOrganisationOfficersText(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        $this->addTextLine(Formatter\OrganisationName::format($publicationLink->getLicence()->getOrganisation()));

        if ($context->offsetExists('licencePeople') && is_array($context->offsetGet('licencePeople'))) {
            $text[] = Formatter\People::format(
                $publicationLink->getLicence()->getOrganisation(),
                $context->offsetGet('licencePeople')
            );
            $this->addTextLine(implode(', ', $text));
        }
    }
}
