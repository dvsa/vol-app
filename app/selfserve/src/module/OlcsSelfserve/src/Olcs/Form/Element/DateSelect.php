<?php
/**
 * OLCS DateSelect Form Element
 *
 * Differs from Zend's as it haves a textbox for the year value rather than a select box
 *
 * @package    olcs
 * @subpackage form
 * @author     Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Form\Element;

use Zend\Form\Element\Text as ZendText;

class DateSelect extends \Zend\Form\Element\DateSelect
{
    /**
     * Constructor. Changes the year element.
     *
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     */
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);

        $this->yearElement = new ZendText('year');

        if (!empty($options)) {
            $this->setOptions($options);
        }
    }
}
