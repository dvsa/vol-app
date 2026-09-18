<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\LongText;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/long-text/create")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Regex", options={"pattern":"/^[a-z0-9]+(-[a-z0-9]+)*$/"})
     */
    protected $referenceKey;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack":{"en_GB","cy_GB","en_NI","cy_NI"}})
     */
    protected $locale;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1,"max":255})
     */
    protected $pageName;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $description;

    protected $content;

    public function getReferenceKey(): ?string
    {
        return $this->referenceKey;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getPageName(): ?string
    {
        return $this->pageName;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
}
