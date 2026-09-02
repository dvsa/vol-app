<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\LongText;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * The reference key is deliberately absent: application code addresses content
 * by it, so it is set once at creation and never edited.
 *
 * @Transfer\RouteName("backend/long-text/update")
 * @Transfer\Method("PUT")
 */
final class Update extends AbstractCommand
{
    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    protected $id;

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

    public function getId(): ?int
    {
        return $this->id === null ? null : (int) $this->id;
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
