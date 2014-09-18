<?php

/**
 * Case Prohibition Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Case Prohibition Controller
 */
class CaseAnnualTestHistoryController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'case';

    /**
     * This property identifies the field in the database for the comments box
     * to save to. A comments box is automatically added to the index page. If
     * this property is null or blank then no comments box is rendered.
     *
     * @var unknown
     */
    protected $commentBoxDbFieldName = 'athComments';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case';

    /**
     * For most case crud controllers, we use the case/inner-layout
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'case/inner-layout';

    /**
     * Holds the isAction
     *
     * @var boolean
     */
    protected $isAction = false;

    public function buildCommentsBoxIntoView()
    {

        $commentsBoxForm;
        $this->setPlaceholder('commentsForm', $commentsBoxForm);
    }
}
