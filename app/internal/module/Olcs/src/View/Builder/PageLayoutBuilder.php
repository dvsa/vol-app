<?php

namespace Olcs\View\Builder;

use Zend\View\Model\ViewModel;

/**
 * Class PageLayoutBuilder
 * @package Olcs\View\Builder
 */
final class PageLayoutBuilder extends AbstractBuilder
{
    /**
     * @var BuilderInterface
     */
    private $wrapped;

    /**
     * @var string
     */
    private $pageLayout;

    /**
     * @param BuilderInterface $wrapped
     * @param string $pageLayout
     */
    public function __construct(BuilderInterface $wrapped, $pageLayout)
    {
        $this->wrapped = $wrapped;
        $this->pageLayout = $pageLayout;
    }

    /**
     * @param ViewModel $view
     * @return ViewModel
     */
    protected function decorateView(ViewModel $view)
    {
        $layout = new ViewModel();
        $layout->setTemplate($this->pageLayout);

        $layout->addChild($view, 'content');

        return $this->wrapped->buildView($layout);
    }
}
