<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

/**
 * Class PenaltyCommentBox
 * @package Olcs\Data\Mapper
 */
class PenaltyCommentBox extends AbstractCommentMapper implements MapperInterface
{
    const COMMENT_FIELD = 'penaltiesNote'; //needs to be constant as methods called statically
}
