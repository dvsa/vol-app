<?php

/**
 * Table Element
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\Elements\Types;

use Laminas\Form\Element;

/**
 * Table Element
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Table extends Element
{
    /**
     * Hold the table
     *
     * @var object
     */
    private $table;

    /**
     * Setter for table
     *
     * @param object $table
     */
    public function setTable($table, $fieldset = null): void
    {
        $this->table = $table;

        if (is_null($fieldset)) {
            $fieldset = $this->getName();
        }

        $table->setFieldset($fieldset);

        // let our table builder know if the element itself
        // is disabled; it can use this to make certain
        // rendering decisions
        $table->setDisabled(
            $this->hasAttribute('disabled')
        );
    }

    /**
     * Get the table
     *
     * @return object
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Render the table
     *
     * @return string
     */
    public function render()
    {
        return $this->table->render();
    }
}
