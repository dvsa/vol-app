<?php

namespace Common\View\Helper;

use Laminas\Http\Request;
use Laminas\View\Helper\AbstractHelper;

/**
 * Create a link '< Back'
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class LinkBack extends AbstractHelper
{
    public function __construct(
        /** @var  \Laminas\Http\PhpEnvironment\Request */
        private Request $request
    ) {
    }

    /**
     * Return a back link
     *
     * @param array|null $params Parameters
     *
     * @return string
     */
    public function __invoke(array $params = null)
    {
        if (empty($params['url'])) {
            /** @var \Laminas\Http\Header\Referer $header */
            $header = $this->request->getHeader('referer');

            if ($header === false) {
                return '';
            }

            $url = $header->uri()->getPath();
        } else {
            $url = $params['url'];
        }

        $label = ($params['label'] ?? 'common.link.back.label');
        $isNeedEscape = (!isset($params['escape']) || $params['escape'] !== false);

        $label = $this->view->translate($label);

        return
            '<a href="' . $url . '" class="govuk-back-link">' .
                ($isNeedEscape ? htmlspecialchars($label) : $label) .
            '</a>';
    }
}
