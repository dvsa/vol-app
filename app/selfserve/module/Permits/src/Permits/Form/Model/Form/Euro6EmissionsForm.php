<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("Euro6Emissions")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class Euro6EmissionsForm
{
        /**
         * @Form\Name("Fields")
         * @Form\Options({
         *     "label" : "permits.page.euro6.emissions.question",
         *     "hint" : "permits.page.euro6.emissions.info"
         * })
         * @Form\ComposedObject("Permits\Form\Model\Fieldset\Euro6Emissions")
         */
        public $fields = null;

        /**
         * @Form\Name("Submit")
         * @Form\ComposedObject("Permits\Form\Model\Fieldset\Submit")
         */
        public $submitButton = null;

}
