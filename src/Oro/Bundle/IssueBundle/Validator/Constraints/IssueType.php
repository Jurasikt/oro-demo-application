<?php

namespace Oro\Bundle\IssueBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class IssueType extends Constraint
{
    public $message = 'Not valid issue type "%issue_type%"';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'oro_issue.validator.issue_type';
    }
}
