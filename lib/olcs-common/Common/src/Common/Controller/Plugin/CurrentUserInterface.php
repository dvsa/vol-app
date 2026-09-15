<?php

namespace Common\Controller\Plugin;

/**
 * Interface CurrentUserInterface
 * @package Common\Controller\Plugin
 */
interface CurrentUserInterface
{
    /**
     * @return array
     */
    public function getUserData();
}
