<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Complaint")
 * @Form\Attributes({"method":"post"})
 * @Form\InputFilter("Common\Form\InputFilter")
 */
class Complaint
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $case = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Name("complainant-details")
     * @Form\Options({"label":"Complainant details"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ComplainantDetails")
     */
    public $complainantDetails = null;

    /**
     * @Form\Name("complaint-details")
     * @Form\Options({"label":"Complaint details"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ComplaintDetails")
     */
    public $complaintDetails = null;

    /**
     * @Form\Name("organisation-details")
     * @Form\Options({"label":"Operator details","class":"extra-long"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OrganisationDetails")
     */
    public $organisationDetails = null;

    /**
     * @Form\Name("driver-details")
     * @Form\Options({"label":"Driver details","class":"extra-long"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\DriverDetails")
     */
    public $driverDetails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
