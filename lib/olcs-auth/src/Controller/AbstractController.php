<?php

namespace Dvsa\Olcs\Auth\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

/**
 * Abstract Controller
 */
class AbstractController extends AbstractActionController
{
    /**
     * Check if a button is pressed
     *
     * @param string $button Button name
     *
     * @return boolean
     */
    protected function isButtonPressed($button)
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        }

        return isset($data[$button]);
    }
}
