<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\Application;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\ProcessInterface;

/**
 * Class Text1
 * @package Dvsa\Olcs\Api\Service\Publication\Process\Application
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class Text1 implements ProcessInterface
{
    protected $previousPublication = '(%s)';

    /**
     * @param PublicationLink $publicationLink
     * @param ImmutableArrayObject $context
     */
    public function process(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        $text = $publicationLink->getLicence()->getLicNo() . ' ' .
            $publicationLink->getApplication()->getLicenceTypeShortCode();

        if (
            $publicationLink->getApplication()->isGoods() &&
            $context->offsetExists('previousPublication')
        ) {
            $text .= "\n" . sprintf($this->previousPublication, $context->offsetGet('previousPublication'));
        }

        $publicationLink->setText1($text);
    }
}
