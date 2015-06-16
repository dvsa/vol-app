<?php

namespace Olcs\View\Builder;

use Zend\View\Model\ViewModel;

/**
 * Interface BuilderInterface
 * @package Olcs\View\Builder
 */
interface BuilderInterface
{
    /**
     * allow for very simple views to be passed as a string. Obviously this
     * precludes the passing of any template variables but can still come
     * in handy when no extra variables need to be set
     *
     * @param string $template
     * @return ViewModel
     */
    public function buildViewFromTemplate($template);

    /**
     * @param ViewModel $view
     * @return ViewModel
     */
    public function buildView(ViewModel $view);
}
