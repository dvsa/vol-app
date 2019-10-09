<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("user-settings")
 */
class UserSettings
{
    /**
     * @Form\Attributes({"id":"translateToWelsh","placeholder":""})
     * @Form\Options({
     *     "label": "translate-to-welsh",
     *     "checked_value":"Y",
     *     "unchecked_value":"N"
     * })
     * @Form\Type("OlcsCheckbox")
     */
    public $translateToWelsh = null;

    /**
     * @Form\Attributes({"id":"osType"})
     * @Form\Options({
     *     "label": "Operating System",
     *     "value_options": {
     *         "os_type_windows_7": "Windows 7",
     *         "os_type_windows_10": "Windows 10"
     *     }
     * })
     * @Form\Type("Select")
     */
    public $osType = null;
}
