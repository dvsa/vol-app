<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section;

use Dvsa\Olcs\Snapshot\Service\Snapshots\FormatReviewDataTrait;
use Laminas\I18n\Translator\TranslatorInterface;

abstract class AbstractReviewService implements ReviewServiceInterface
{
    use FormatReviewDataTrait;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * Create service instance
     *
     *
     * @return AbstractReviewService
     */
    public function __construct(AbstractReviewServiceServices $abstractReviewServiceServices)
    {
        $this->translator = $abstractReviewServiceServices->getTranslator();
    }
}
