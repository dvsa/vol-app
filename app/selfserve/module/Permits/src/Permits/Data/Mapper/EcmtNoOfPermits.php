<?php

namespace Permits\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;

/**
 * ECMT No of permits mapper
 */
class EcmtNoOfPermits
{
    /** @var TranslationHelperService */
    private $translator;

    /**
     * Create service instance
     *
     * @param TranslationHelperService $translator
     *
     * @return EcmtNoOfPermits
     */
    public function __construct(TranslationHelperService $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function mapForDisplay(array $data)
    {
        return $this->createPermitsRequiredLines(
            [
                'permits.page.fee.emissions.category.euro5' => $data['requiredEuro5'],
                'permits.page.fee.emissions.category.euro6' => $data['requiredEuro6']
            ]
        );
    }

    /**
     * Return an array with each element representing the number of permits required for a given emissions category
     *
     * @param array $lineSources
     *
     * @return array
     */
    private function createPermitsRequiredLines(array $lineSources)
    {
        $lines = [];

        foreach ($lineSources as $categoryNameTranslationKey => $permitsRequiredCount) {
            $lineTranslationKey = null;
            if ($permitsRequiredCount > 1) {
                $lineTranslationKey = 'permits.page.fee.number.permits.line.multiple';
            } elseif ($permitsRequiredCount == 1) {
                $lineTranslationKey = 'permits.page.fee.number.permits.line.single';
            }

            if (is_string($lineTranslationKey)) {
                $categoryName = $this->translator->translate($categoryNameTranslationKey);

                $lines[] = $this->translator->translateReplace(
                    $lineTranslationKey,
                    [$permitsRequiredCount, $categoryName]
                );
            }
        }

        return $lines;
    }
}
