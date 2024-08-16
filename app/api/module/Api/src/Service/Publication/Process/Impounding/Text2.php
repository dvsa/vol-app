<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\Impounding;

use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\AbstractText;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;

/**
 * Class Impounding Text2
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class Text2 extends AbstractText
{
    protected $text2 =
        'The applicants listed above have applied for the return of %s under the following; %s';

    /**
     * @param PublicationLink $publicationLink
     * @param ImmutableArrayObject $context
     * @return PublicationLink
     */
    public function process(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        $this->addTextLine(
            sprintf(
                $this->text2,
                $publicationLink->getImpounding()->getVrm(),
                $this->getImpoundingLegislationString($publicationLink)
            )
        );
        $publicationLink->setText2($this->getTextWithNewLine());

        return $publicationLink;
    }

    /**
     * Get all the impounding legislation as single string.
     *
     * @return string
     */
    private function getImpoundingLegislationString(PublicationLink $publicationLink)
    {
        $text = [];
        $legislationCollection = $publicationLink->getImpounding()->getImpoundingLegislationTypes();

        /** @var RefDataEntity $legislation */
        foreach ($legislationCollection as $legislation) {
            $text[] = $legislation->getDescription();
        }

        return implode('; ', $text);
    }
}
