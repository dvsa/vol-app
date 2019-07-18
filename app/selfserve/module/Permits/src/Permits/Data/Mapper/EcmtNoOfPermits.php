<?php

namespace Permits\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * ECMT No of permits mapper
 */
class EcmtNoOfPermits
{
    /**
     * @param array $data
     * @param TranslationHelperService $translator
     * @param Url $url
     *
     * @return array
     */
    public static function mapForDisplay(array $data, TranslationHelperService $translator, Url $url)
    {
        return self::createPermitsRequiredLines(
            [
                'permits.page.fee.emissions.category.euro5' => $data['requiredEuro5'],
                'permits.page.fee.emissions.category.euro6' => $data['requiredEuro6']
            ],
            $translator
        );
    }

    /**
     * Return an array with each element representing the number of permits required for a given emissions category
     *
     * @param array $lineSources
     * @param TranslationHelperService $translator
     *
     * @return array
     */
    private static function createPermitsRequiredLines(array $lineSources, TranslationHelperService $translator)
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
                $categoryName = $translator->translate($categoryNameTranslationKey);

                $lines[] = $translator->translateReplace(
                    $lineTranslationKey,
                    [$permitsRequiredCount, $categoryName]
                );
            }
        }

        return $lines;
    }
}
