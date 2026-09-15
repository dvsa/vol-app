<?php

namespace Common\View\Helper;

use Common\Service\Helper\TranslationHelperService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Helper\AbstractHelper;

/**
 * Class return translateReplace to view
 */
class TranslateReplace extends AbstractHelper
{
    public function __construct(private TranslationHelperService $translator)
    {
    }

    /**
     * Allows you to replace variables after the string is translated
     *
     * @param string $translationKey
     * @param string $translateToWelsh 'Y' or 'N', Force the translation into welsh
     * @return string
     */
    public function __invoke($translationKey, array $arguments, $translateToWelsh = 'N')
    {
        return $this->translator->translateReplace($translationKey, $arguments, $translateToWelsh);
    }
}
