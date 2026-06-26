<?php

namespace Common\Form\View\Helper\Readonly;

use Common\Form\Elements\Types\FileUploadList;
use Common\Form\Elements\Types\FileUploadListItem;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\View\Helper\AbstractHelper;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class FormFileUploadList extends AbstractHelper
{
    private static $htmlCntr =
        '<div class="help__text"><h3 class="file__heading">%s</h3><ul class="js-upload-list">%s</ul></div>';

    /**
     * Invoke helper as function. Proxies to {@link render()}.
     *
     * @param FieldsetInterface|null $element Element
     *
     * @return string
     */
    public function __invoke(FieldsetInterface $element = null)
    {
        return $this->render($element);
    }

    /**
     * Render element
     *
     * @param FileUploadList $fs File Upload List fieldset element
     *
     * @return string
     */
    public function render(FieldsetInterface $fs)
    {
        if ($fs->count() == 0) {
            return '';
        }

        $fsHtml = [];
        foreach ($fs->getIterator() as $fieldset) {
            if (!($fieldset instanceof FileUploadListItem)) {
                continue;
            }

            $elmHtml = [];
            /** @var \Laminas\Form\ElementInterface $elm */
            foreach ($fieldset->getIterator() as $elm) {
                $elm->setOption('disable_html_escape', true);

                $elmHtml[] = $this->view->plugin('readonlyformitem')->render($elm);
            }

            $fsHtml[] = '<li class="file">' . implode('', $elmHtml) . '</li>';
        }

        return sprintf(
            self::$htmlCntr,
            $this->view->translate('common.file-upload.table.col.FileName'),
            implode('', $fsHtml)
        );
    }
}
