<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section;

use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Abstract Review Service Services
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AbstractReviewServiceServices
{
    /**
     * Create service instance
     *
     *
     * @return AbstractReviewServiceServices
     */
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Return the translator service
     *
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }
}
