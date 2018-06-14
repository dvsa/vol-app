<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("Euro6Emissions")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Permits\Form\Form")
 */
class Euro6EmissionsForm
{
       /* $this->add(array(
            'type' => 'Radio',
            'name' => 'meetsEuro6',
            'options' => array(
                'label' => '',
                'label_attributes' => array(
                    'class' => 'form-control form-control--radio euro6Radio',
                ),
                'value_options' => array(
                    '1' => 'Yes',
                    '0' => 'No',
                ),
            ),
        ));*/

        /**
         * @Form\Name("meetsEuro6")
         * @Form\Required(true)
         * @Form\Attributes({
         *   "class" : "input--trips",
         * })
         * @Form\Options({
         *     "label": "",
         *     "label_attributes":{
         *          "class" : "form-control form-control--radio restrictedRadio"
         *     },
         *     "value_options":{
         *          "1" : "Yes",
         *          "0" : "No"
         *     }
         * })
         * @Form\Type("Radio")
         */
        public $meetsEuro6 = null;

        /**
         * @Form\Name("submit")
         * @Form\Attributes({
         *     "class":"action--primary large",
         *     "id":"submitbutton",
         *     "value":"Save and continue",
         * })
         * @Form\Type("Zend\Form\Element\Submit")
         */
        public $submitButton = null;

}
