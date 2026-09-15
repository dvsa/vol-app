<?php

namespace Common\Service\Table\Formatter;

use Laminas\View\HelperPluginManager;

class DashboardTmApplicationStatus implements FormatterPluginManagerInterface
{
    public function __construct(private HelperPluginManager $viewHelperManager)
    {
    }

    /**
     * Generate the HTML to display the TM Application status
     *
     * @param  array $data
     * @param  array $column
     * @return string HTML
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $viewHelper = $this->viewHelperManager->get('transportManagerApplicationStatus');

        return
            $viewHelper->render(
                $data['transportManagerApplicationStatus']['id'],
                $data['transportManagerApplicationStatus']['description']
            );
    }
}
