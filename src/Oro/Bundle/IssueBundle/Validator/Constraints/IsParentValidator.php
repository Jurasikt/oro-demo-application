<?php

namespace Oro\Bundle\IssueBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\IssueBundle\Placeholder\PlaceholderFilter;

class IsParentValidator extends ConstraintValidator
{
    /**
     * @var ExecutionContextInterface
     */
    protected $context;

    /**
     * @var PlaceholderFilter
     */
    protected $placeholderFilter;

    public function __construct(PlaceholderFilter $placeholderFilter)
    {
        $this->placeholderFilter = $placeholderFilter;
    }

    /**
     * @param Issue $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Issue) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Oro\Bundle\IssueBundle\Entity\Issue supported only, but %s given',
                    is_object($value) ? get_class($value) : gettype($value)
                )
            );
        }

        if (! $this->placeholderFilter->isAddChildApplicable($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%issue%', $value->getIssueType())
                ->addViolation();
        }
    }
}
