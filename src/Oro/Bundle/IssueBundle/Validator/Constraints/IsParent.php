<?php

namespace Oro\Bundle\IssueBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class IsParent extends Constraint
{
    public $message = 'Can not add child to "%issue%"';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'oro_issue.validator.is_parent';
    }
}
