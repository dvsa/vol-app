<?php

/**
 * New Tm User
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;
use Common\Service\Helper\FlashMessengerHelperService;
use Laminas\Form\Form;

/**
 * New Tm User
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class NewTmUser implements MapperInterface
{
    /**
     * @return array
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        return $data;
    }

    public static function mapFormErrors(Form $form, array $errors, FlashMessengerHelperService $fm): void
    {
        $formMessages = [];

        if (isset($errors['username'])) {
            foreach ($errors['username'] as $message) {
                $formMessages['data']['username'][] = $message;
            }

            unset($errors['username']);
        }

        if (isset($errors['emailAddress'])) {
            foreach ($errors['emailAddress'] as $message) {
                $formMessages['data']['emailAddress'][] = $message;
            }

            unset($errors['emailAddress']);
        }

        foreach ($errors as $error) {
            $fm->addCurrentErrorMessage($error);
        }

        $form->setMessages($formMessages);
    }
}
