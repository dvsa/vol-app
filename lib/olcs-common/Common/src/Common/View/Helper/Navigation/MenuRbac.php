<?php

namespace Common\View\Helper\Navigation;

use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Page\AbstractPage;
use Laminas\View\Helper\Navigation\Menu;

/**
 * Navigation Menu with RBAC functions
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class MenuRbac extends Menu
{
    /**
     * View helper entry point:
     * Retrieves helper and optionally sets container to operate on
     *
     * @param AbstractContainer $container [optional] container to operate on
     *
     * @return self
     */
    #[\Override]
    public function __invoke($container = null)
    {
        parent::__invoke($container);

        $this->filter();

        return $this;
    }

    /**
     * Filter pages by RBAC
     *
     * @param AbstractPage $container Page
     *
     * @return $this
     */
    public function filter(AbstractContainer $container = null)
    {
        if (!$container instanceof \Laminas\Navigation\AbstractContainer) {
            $container = $this->getContainer();
        }

        $container->setPages(
            array_filter(
                $container->getPages(),
                fn(AbstractPage $item) => $this->accept($item, false)
            )
        );

        return $this;
    }
}
