<?php

namespace Oro\Bundle\IssueBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\IssueBundle\Entity\Manager\IssueManager;

class IssueTypeValidator extends ConstraintValidator
{
    /**
     * @var ExecutionContextInterface
     */
    protected $context;

    /**
     * @var IssueManager
     */
    protected $issueManager;

    public function __construct(IssueManager $issueManager)
    {
        $this->issueManager = $issueManager;
    }

    /**
     * @param string $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $issue = $this->context->getRoot()->getData();

        if (!$issue instanceof Issue) {
            throw new \InvalidArgumentException(
                sprintf(
                    'IssueTypeValidator supported only Oro\Bundle\IssueBundle\Entity\Issue, but %s given',
                    is_object($value) ? get_class($value) : gettype($value)
                )
            );
        }

        if ($value != Issue::ISSUE_TYPE_STORY
            && $this->issueManager->getChild($issue)
        ) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%issue_type%', $value)->addViolation();
        }
    }
}
