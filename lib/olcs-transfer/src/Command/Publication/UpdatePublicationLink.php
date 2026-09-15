<?php

/**
 * Publish a publication
 */

namespace Dvsa\Olcs\Transfer\Command\Publication;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/publication/link/single")
 * @Transfer\Method("PUT")
 */
final class UpdatePublicationLink extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":4000})
     * @Transfer\Optional
     */
    public $text1;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":4000})
     * @Transfer\Optional
     */
    public $text2;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":4000})
     * @Transfer\Optional
     */
    public $text3;

    /**
     * @return string
     */
    public function getText1()
    {
        return $this->text1;
    }

    /**
     * @return string
     */
    public function getText2()
    {
        return $this->text2;
    }

    /**
     * @return string
     */
    public function getText3()
    {
        return $this->text3;
    }
}
