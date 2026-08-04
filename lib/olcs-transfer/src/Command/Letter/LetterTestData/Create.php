<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterTestData;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/letter/letter-test-data")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":255})
     */
    protected $name;

    /**
     * @var array
     */
    protected $json;


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getJson()
    {
        return $this->json;
    }
}
