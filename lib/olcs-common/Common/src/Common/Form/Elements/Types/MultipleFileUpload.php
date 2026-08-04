<?php

/**
 * Multiple File Upload Element
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\Elements\Types;

use Laminas\Form\Fieldset;
use Laminas\Form\Element;
use Common\Form\Elements\InputFilters\File;

/**
 * Multiple File Upload Element
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class MultipleFileUpload extends Fieldset
{
    /**
     * Add fields to the fieldset
     *
     * @param string $name
     * @param array $options
     */
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);

        $this->setLabel('Upload file');

        $list = new FileUploadList('list');

        $this->add($list);

        $messages = new Element\Hidden('__messages__');
        $this->add($messages);

        $fileControlFieldset = new Fieldset('file-controls');
        $fileControlFieldset->setAttribute('class', 'field');

        $uploader = new File('file', ['render-container' => false]);
        $uploader->setAttribute('class', 'file-upload');

        $button = new Element\Submit('upload', ['render-container' => false]);
        $button->setValue('Upload');
        $button->setAttribute('class', 'govuk-button');

        $fileControlFieldset->add($uploader);
        $fileControlFieldset->add($button);

        $this->add($fileControlFieldset);
    }
}
