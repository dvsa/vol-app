<?php
/**
 * Generic form used to track added and removed ID:s in a list of entities
 *
 * @package    olcs
 * @subpackage application
 * @author     Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Form\Application;

use Zend\Form\Form;

class IdListForm extends Form
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);

        $this->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'listIds',
            'options' => array(
                'count' => 0,
                'target_element' => array(
                    'type' => 'Zend\Form\Element\Hidden',
                ),
            ),
        ));
    }
}
