<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterType;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Render a proposed letter type composition without saving it.
 *
 * A command rather than a query because the composition is too large to carry in a query string,
 * and every Query DTO in this library is a GET. Nothing is written -- the handler builds a
 * transient LetterInstance and discards it.
 *
 * @Transfer\RouteName("backend/letter/letter-type/preview-composition")
 * @Transfer\Method("POST")
 */
final class PreviewComposition extends AbstractCommand
{
    /**
     * The letter type being composed. Supplies the master template, and the saved composition
     * when the caller sends none.
     *
     * @var int
     * @Transfer\Filter("Laminas\Filter\ToInt")
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    protected $letterType;

    /**
     * Section ids in composition order. Order is the display order, matching how
     * LetterType\Update derives display_order from array position on save.
     *
     * @var array
     * @Transfer\Optional
     * @Transfer\ArrayInput
     */
    protected $sections;

    /**
     * Section ids the composition marks as required. Drives whether an unresolved section is
     * reported as blocking or advisory.
     *
     * @var array
     * @Transfer\Optional
     * @Transfer\ArrayInput
     */
    protected $sectionsRequired;

    /**
     * @var array
     * @Transfer\Optional
     * @Transfer\ArrayInput
     */
    protected $appendices;

    /**
     * Issue ids to include in the preview, so an admin can see how issue wording sits in the letter.
     *
     * @var array
     * @Transfer\Optional
     * @Transfer\ArrayInput
     */
    protected $issues;

    /**
     * Licence to draw bookmark values from. Optional: without it the letter still renders and
     * unresolvable bookmarks are reported.
     *
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\ToInt")
     */
    protected $licence;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\ToInt")
     */
    protected $application;

    /**
     * Variant context. Each defaults from the chosen licence or application but may be overridden,
     * so an admin can exercise combinations no real record happens to cover.
     *
     * @var string
     * @Transfer\Optional
     */
    protected $goodsOrPsv;

    /**
     * Tri-state: null means "not known", which is what a licence-only letter supplies and is why a
     * variant pinning is_variation can never match one.
     *
     * @var bool
     * @Transfer\Optional
     */
    protected $isVariation;

    /**
     * @var bool
     * @Transfer\Optional
     */
    protected $isNi;

    /**
     * @var string
     * @Transfer\Optional
     */
    protected $organisationType;

    /**
     * @var array
     * @Transfer\Optional
     * @Transfer\ArrayInput
     */
    protected $selectedChoiceIds;

    /**
     * Render the full master template chrome. Off by default: the chrome costs roughly 200KB and a
     * few hundred milliseconds per render, dominated by re-purifying the inline logo, and it does
     * not change while an admin is composing.
     *
     * @var bool
     * @Transfer\Optional
     */
    protected $withChrome;

    public function getLetterType()
    {
        return $this->letterType;
    }

    public function getSections()
    {
        return $this->sections;
    }

    public function getSectionsRequired()
    {
        return $this->sectionsRequired;
    }

    public function getAppendices()
    {
        return $this->appendices;
    }

    public function getIssues()
    {
        return $this->issues;
    }

    public function getLicence()
    {
        return $this->licence;
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function getGoodsOrPsv()
    {
        return $this->goodsOrPsv;
    }

    public function getIsVariation()
    {
        return $this->isVariation;
    }

    public function getIsNi()
    {
        return $this->isNi;
    }

    public function getOrganisationType()
    {
        return $this->organisationType;
    }

    public function getSelectedChoiceIds()
    {
        return $this->selectedChoiceIds;
    }

    public function getWithChrome()
    {
        return $this->withChrome;
    }
}
