<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;
use Common\Form\Model\Form\Traits\IdTrait;
use Common\Form\Model\Form\Traits\VersionTrait;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("exception-details")
 */
class ExceptionDetails
{
    use IdTrait;
    use VersionTrait;

    /**
     * @Form\Name("teamOrUser")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "fieldset-attributes": {
     *          "id": "fieldset-team-or-user",
     *          "class": "checkbox"
     *      },
     *      "fieldset-data-group": "operator-type",
     *      "value_options":{
     *          "team":"Team",
     *          "user":"User"
     *      }
     * })
     * @Form\Type("Radio")
     */
    public $teamOrUser = null;

    /**
     * @Form\Name("team")
     * @Form\Type("Hidden")
     */
    public $team = null;
}
