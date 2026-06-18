<?php

namespace Common\Form\View\Helper\Readonly;

use Common\Form\Elements;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\View\Helper\AbstractHelper;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class FormFieldset extends AbstractHelper
{
    protected $classMap = [
        Elements\Types\FileUploadList::class => FormFileUploadList::class,
    ];

    /**
     * Invoke helper as function. Proxies to {@link render()}.
     *
     * @param FieldsetInterface|null $fieldset Element
     *
     * @return string
     */
    public function __invoke(FieldsetInterface $fieldset = null)
    {
        return $this->render($fieldset);
    }

    /**
     * Render element
     *
     * @param FieldsetInterface $fieldset Element
     *
     * @return string
     */
    public function render(FieldsetInterface $fieldset)
    {
        /** @var \Common\Form\View\Helper\FormCollection $helper */
        $helper = $this->view->plugin('FormCollection');

        foreach ($this->classMap as $class => $plugin) {
            if ($fieldset instanceof $class) {
                $helper = $this->view->plugin($plugin);
                break;
            }
        }

        return $helper->render($fieldset);
    }
}
