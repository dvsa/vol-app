<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterType;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Suggest records that would exercise a composition's specific variants.
 *
 * A command for the same reason PreviewComposition is one: the unsaved composition
 * travels with the request, and it is too large for a query string. Nothing is
 * written.
 *
 * @Transfer\RouteName("backend/letter/letter-type/suggest-preview-records")
 * @Transfer\Method("POST")
 */
final class SuggestPreviewRecords extends AbstractCommand
{
    /**
     * The letter type being composed. Supplies the saved composition when the caller
     * sends no sections.
     *
     * @var int
     * @Transfer\Filter("Laminas\Filter\ToInt")
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    protected $letterType;

    /**
     * Section ids in composition order, matching what is on screen.
     *
     * @var array
     * @Transfer\Optional
     * @Transfer\ArrayInput
     */
    protected $sections;

    public function getLetterType()
    {
        return $this->letterType;
    }

    public function getSections()
    {
        return $this->sections;
    }
}
