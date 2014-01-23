<?php

/**
 * Validator for at least one form input value
 *
 * @author adminmwc
 */

namespace Olcs\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;
use Zend\Validator\AbstractValidator;

class FormEmptyValidator extends AbstractValidator {
    
    public $filterOut = array();
    
    private $filtered = array(['submit']);
    
    public function isValid($elements) {
        
        /*$this->filtered = array_merge($this->filtered, $filtered);
        foreach($this->filtered as $filter) {
            unset($elements[$filter]);
        }*/
        $validator = new NotEmpty();
        unset($elements['submit']); // Filter out submit
        foreach ($elements as $label => $var) {
            if ($validator->isValid($var)) {
                return true;
            }
        }
        return false;
        
    }
    
    }
