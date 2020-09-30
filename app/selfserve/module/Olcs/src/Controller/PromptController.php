<?php
namespace Olcs\Controller;

use Olcs\Controller\AbstractSelfserveController;

class PromptController extends AbstractSelfserveController
{
    protected $templateConfig = [
        'default' => 'pages/prompt',
    ];

    /**
     * {@inheritdoc}
     */
    public function checkConditionalDisplay()
    {
        if (!$this->currentUser()->getUserData()['eligibleForPrompt']) {
            return $this->conditionalDisplayNotMet('dashboard');
        }
    }
}
