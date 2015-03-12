<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Type("Zend\Form\Fieldset")
 */
class DiscsNumbering
{
    /**
     * @Form\Name("startNumber")
     * @Form\Type("Text")
     */
    public $startNumber = null;

    /**
     * @Form\Name("endNumber")
     * @Form\Type("Text")
     */
    public $endNumber = null;

    /**
     * @Form\Name("totalPages")
     * @Form\Type("Text")
     */
    public $totalPages = null;

    /**
     * @Form\Name("originalEndNumber")
     * @Form\Type("Hidden")
     */
    public $originalEndNumber = null;

    /**
     * @Form\Name("endNumberIncreased")
     * @Form\Type("Hidden")
     */
    public $endNumberIncreased = null;
}
