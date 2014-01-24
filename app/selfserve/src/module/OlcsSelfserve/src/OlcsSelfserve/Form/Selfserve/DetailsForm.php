<?php
/**
 * A form used to 
 *
 * @package    olcs
 * @subpackage application
 * @author     Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Form\Application;

use Zend\Form\Form;

class DetailsForm extends Form
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct('applicationDetailsForm');
        $this->setAttribute('class', 'application-details-form form-horizontal');
        $this->setAttribute('action', '/application/new/details');
    }
}
