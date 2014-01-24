<?php
/**
 * Subsidiary Search Form
 *
 * @package    olcs
 * @subpackage application
 * @author     Jess Rowbottom <jess.rowbottom@valtech.se>
 */

namespace Olcs\Form\Application;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Stdlib\ArrayUtils;

class SubsidiarySearchForm extends Form  implements InputFilterProviderInterface
{
    /**
     * Defines which basic form elements that are required and how to filter their values
     */
    public function getInputFilterSpecification()
    {
        return array(
            'companyName' => array(
                'required' => false,
                'filters'  => array(
                    array('name' => 'Zend\Filter\StringTrim'),
                ),
            ),
            'companyNumber' => array(
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
            parent::__construct($popup.'SubsidiarySearchForm');
        } else {
            parent::__construct('subsidiarySearchForm');
        }
        
        $this->setAttribute('class', 'form-horizontal overlay-form');
        $this->setAttribute('method', 'GET');
        $this->setAttribute('action', '/application/search/subsidiary');

        $this->add(array(
            'label' => $popup.'companyId',
            'name' => 'companyId',
            'type' => 'hidden',
            'attributes' => array(
                'id' => $popup.'companyId',
                'class' => 'primary-id',
            ),
        ));
                
        $this->add(array(
            'label' => $popup.'companyName',
            'name' => 'companyName',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control column-companyname',
                'id' => $popup.'person-companyname',
            ),
        ));

        $this->add(array(
            'label' => $popup.'companyNumber',
            'name' => 'companyNumber',
            'type' => 'text',
            'attributes' => array(
                'class' => 'form-control column-companynumber',
                'id' => $popup.'person-companynumber',
            ),
        ));

        $this->add(array(
            'label' => $popup.'searchCompanyButton',
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'disabled' => 'disabled',
                'class' => 'btn btn-success btn-search',
                'value' => 'Search',
                'id'    => $popup.'searchCompanyButton'
            ),
        ));
        
        $this->add(array(
            'label' => $popup.'saveAndAddAnotherButton',
            'name' => 'saveAndAddAnotherButton',
            'type' => 'Submit',
            'attributes' => array(
                'class' => 'hidden btn btn-success pull-right',
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
