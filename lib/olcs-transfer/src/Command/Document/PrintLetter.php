<?php

namespace Dvsa\Olcs\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/document/single/letter/print")
 * @Transfer\Method("POST")
 */
final class PrintLetter extends AbstractCommand
{
    use Identity;

    public const METHOD_EMAIL = 'email';
    public const METHOD_PRINT_AND_POST = 'printAndPost';

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": {
     *              PrintLetter::METHOD_EMAIL,
     *              PrintLetter::METHOD_PRINT_AND_POST,
     *          },
     *     },
     * )
     * @Transfer\Optional
     */
    protected $method;

    /**
     * Ignore user preferences and always create correspondence inbox for letter.
     *
     * @var bool
     * @Transfer\Optional
     */
    protected $forceCorrespondence = false;

    /**
     * Get Method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get Method
     *
     * @return bool
     */
    public function getForceCorrespondence()
    {
        return $this->forceCorrespondence;
    }
}
