<?php

namespace Common\Form\View\Helper\Readonly;

use Common\Form\Elements\Types\Table;
use Laminas\Form\ElementInterface;
use Laminas\Form\View\Helper\AbstractHelper;

/**
 * Class FormTable
 * @package Common\Form\View\Helper\Readonly
 */
class FormTable extends AbstractHelper
{
    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param ElementInterface|null $element Element
     *
     * @return FormTable|string
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element instanceof \Laminas\Form\ElementInterface) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * Render
     *
     * @param ElementInterface $element Element
     */
    public function render(ElementInterface $element): string
    {
        if (!($element instanceof Table)) {
            return '';
        }

        $table = $element->getTable();

        // remove all checkbox columns
        $columns = $table->getColumns();

        if (is_array($columns)) {
            $newColumns = [];
            foreach ($columns as $column) {
                if (isset($column['type']) && $column['type'] === 'Checkbox') {
                    continue;
                }

                $newColumns[] = $column;
            }

            $table->setColumns($newColumns);
        }

        return $element->render();
    }
}
