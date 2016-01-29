<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Options({
 *     "label": "documents.bookmarks",
 * })
 * @Form\Name("bookmarks")
 */
class GenerateDocumentBookmarks
{
    /**
    * We can't populate our bookmarks statically
    * from config. They're one to many with the
    * template the user chooses, and each bookmark
    * has many child paragraphs. As such we have to
    * build them up in the controller
    */
}
