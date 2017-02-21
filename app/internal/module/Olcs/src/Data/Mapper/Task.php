<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Common\Service\Helper\FlashMessengerHelperService;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class Task implements MapperInterface
{
    /**
     * Map From Result
     *
     * @param array $data Api Data
     *
     * @return void
     */
    public static function mapFromResult(array $data)
    {
    }

    /**
     * Map Errors from API to form
     *
     * @param array                       $errors       Errors
     * @param \Zend\Form\FormInterface    $form         Form
     * @param FlashMessengerHelperService $flashMsgsSrv Flash messenger
     *
     * @return void
     */
    public static function mapFormErrors(
        array $errors,
        \Zend\Form\FormInterface $form,
        FlashMessengerHelperService $flashMsgsSrv
    ) {
        $formMessages = [];

        self::mapApiErrors($errors, $flashMsgsSrv);

        $form->setMessages($formMessages);
    }

    /**
     * Map error messages from API (not assigned to field or special)
     *
     * @param array                       $errors       List of errors from Api
     * @param FlashMessengerHelperService $flashMsgsSrv Flash messenger
     *
     * @return void
     */
    public static function mapApiErrors(
        array $errors,
        FlashMessengerHelperService $flashMsgsSrv
    ) {
        if (empty($errors)) {
            return;
        }

        foreach ($errors as $section => $err) {
            $flashMsgsSrv->addCurrentErrorMessage($err);
        }
    }
}
