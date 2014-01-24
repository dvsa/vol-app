<?php
/**
 * Person Search Form
 *
 * @package    olcs
 * @subpackage application
 * @author     Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Form\Application;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Stdlib\ArrayUtils;

class PersonSearchForm extends Form  implements InputFilterProviderInterface
{
    /**
     * Defines which basic form elements that are required and how to filter their values
     */
    public function getInputFilterSpecification()
    {
        return array(
            'personSurname' => array(
                'required' => false,
                'filters'  => array(
                    array('name' => 'Zend\Filter\StringTrim'),
                ),
            ),
            'personFirstName' => array(
                'required' => false,
                'filters'  => array(
                    array('name' => 'Zend\Filter\StringTrim'),
                ),
            ),
        );
    }

    /**
     * Sets the data for the form
     *
     * Overridden in this form due to ZF 2.2:s inability to correctly
     * detect when complex elements like DateSelect are empty. Therefore
     * cleaning up empty nested arrays and replacing them with NULL-values
     * which ZF 2.2 correctly detect as empty and thus doesn't fail validation
     */
    public function setData($data)
    {
        if ($data instanceof Traversable) {
            $data = ArrayUtils::iteratorToArray($data);
        }

        if (is_array($data)) {
            foreach ($data as $key => $input) {
                if (is_array($input) && !array_filter($input)) {
                    $data[$key] = null;
                }
            }
        }

        parent::setData($data);
    }

    public function __construct($popup = '')
    {
        // if $popup == true, then we alter the id and parameters of the form 
        if ($popup !== '') {
            parent::__construct($popup.'PersonSearchForm');
        } else {
            parent::__construct('personSearchForm');
        }
        
        $this->setAttribute('class', 'form-horizontal overlay-form');
        $this->setAttribute('method', 'GET');
        $this->setAttribute('action', '/application/search/person');

        $this->add(array(
            'label' => $popup.'personId',
            'name' => 'personId',
            'type' => 'hidden',
            'attributes' => array(
                'id' => $popup.'personId',
                'class' => 'primary-id',
            ),
        ));
                
        $this->add(array(
            'label' => $popup.'person-surname',
            'name' => 'personSurname',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control column-surname',
                'id' => $popup.'person-surname',
            ),
        ));

        $this->add(array(
            'label' => $popup.'person-first-name',
            'name' => 'personFirstName',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control column-firstname',
                'id' => $popup.'person-first-name',
            ),
        ));

        $this->add(array(
            'label' => $popup.'personDob[day]',
            'name' => 'personDob',
            'type' => 'Olcs\Form\Element\DateSelect',
            'options' => array(
                'create_empty_option' => true,
                'day_attributes' =>  array(
                    'label' => $popup.'personDob[day]',
                    'id' => $popup.'personDob[day]',
                    'class' => 'multiselect column-dob-day',
                ),
                'month_attributes' =>  array(
                    'label' => $popup.'personDob[month]',
                    'id' => $popup.'personDob[month]',
                    'class' => 'multiselect column-dob-month',
                ),
                'year_attributes' =>  array(
                    'label' => $popup.'personDob[year]',
                    'id' => $popup.'personDob[year]',
                    'class' => 'form-control column-dob-year',
                ),
            ),
        ));

        $this->add(array(
            'label' => $popup.'searchPersonButton',
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'disabled' => 'disabled',
                'class' => 'btn btn-success btn-search',
                'value' => 'Search',
                'id'    => $popup.'searchPersonButton'
            ),
        ));
        
        $this->add(array(
            'label' => $popup.'saveAndAddAnotherButton',
            'name' => 'saveAndAddAnotherButton',
            'type' => 'Submit',
            'attributes' => array(
                'class' => 'hidden btn btn-submit pull-right',
                'value' => 'Save and add another',
                'id'    => $popup.'saveAndAddAnotherButton'
            ),
        ));
                
        $this->add(array(
            'label' => $popup.'saveButton',
            'name' => 'saveButton',
            'type' => 'submit',
            'attributes' => array(
                'class' => 'hidden btn btn-submit pull-right',
                'value' => 'Save',
                'id'    => $popup.'saveButton'
            ),
        ));
                        
        $this->add(array(
            'label' => $popup.'Newbutton',
            'name' => 'new',
            'type' => 'Button',
            'attributes' => array(
                'class' => 'btn btn-success btn-next pull-right',
                'id' => $popup.'Newbutton',
                'value' => 'New'
            )
        ));

        $this->add(array(
            'label' => $popup.'Cancelbutton',
            'name' => 'cancel',
            'type' => 'Button',
            'attributes' => array(
                'class' => 'btn btn-success btn-cancel',
                'id' => $popup.'Cancelbutton',
                'value' => 'Cancel',
            )
        ));

    }
}
