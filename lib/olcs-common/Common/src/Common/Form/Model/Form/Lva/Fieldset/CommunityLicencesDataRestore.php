<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("community-licences-data-restore")
 */
class CommunityLicencesDataRestore
{
    /**
     * @Form\Attributes({"value": "internal.community_licence.confirm_restore_licences"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $confirm;
}
