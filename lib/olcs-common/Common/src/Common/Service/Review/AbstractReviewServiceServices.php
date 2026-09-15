<?php

namespace Common\Service\Review;

use Common\Service\Helper\TranslationHelperService;

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
    public function __construct(private TranslationHelperService $translationHelper)
    {
    }

    /**
     * Return the translation helper service
     */
    public function getTranslationHelper(): TranslationHelperService
    {
        return $this->translationHelper;
    }
}
