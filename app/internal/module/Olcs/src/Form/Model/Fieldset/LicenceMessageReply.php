<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset;

use Common\Form\Model\Fieldset\MultipleFileUpload;
use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Textarea;
use Laminas\Validator\StringLength;
use Laminas\Validator\NotEmpty;

class LicenceMessageReply
{
    #[Form\Attributes(['class' => 'extra-long'])]
    #[Form\Options(['label' => 'You can enter up to 1000 characters'])]
    #[Form\Type(\Laminas\Form\Element\Textarea::class)]
    #[Form\Filter(\Laminas\Filter\StringTrim::class)]
    #[Form\Validator(NotEmpty::class, options: ['messages' => [NotEmpty::IS_EMPTY => 'messaging.form.message.content.empty.error_message']], breakChainOnFailure: true)]
    #[Form\Validator(StringLength::class, options: ['min' => 5, 'max' => 1000, 'messages' => [StringLength::TOO_SHORT => 'messaging.form.message.content.too_short.error_message', StringLength::TOO_LONG => 'messaging.form.message.content.too_long.error_message']])]
    public ?TextArea $reply = null;

    #[Form\Name('file')]
    #[Form\ComposedObject(MultipleFileUpload::class)]
    public ?MultipleFileUpload $file = null;

    #[Form\Name('actions')]
    #[Form\ComposedObject(LicenceMessageReplyActions::class)]
    public ?LicenceMessageReplyActions $actions = null;
}
