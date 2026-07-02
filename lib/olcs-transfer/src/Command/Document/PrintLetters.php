<?php

namespace Dvsa\Olcs\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;

/**
 * @Transfer\RouteName("backend/document/letter/print")
 * @Transfer\Method("POST")
 */
class PrintLetters extends AbstractCommand
{
    use Ids;

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
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }
}
