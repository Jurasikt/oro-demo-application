<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Validator;

use Oro\Bundle\IssueBundle\Validator\Constraints\IsParentValidator;
use Symfony\Component\Validator\Constraint;

class IsParentValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IsParentValidator
     */
    protected $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $placeholderFilter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $constraint;

    public function setUp()
    {
        $this->constraint = $this->getMockBuilder('Symfony\Component\Validator\Constraint')
            ->disableOriginalConstructor()
            ->getMock();

        $this->placeholderFilter = $this->getMockBuilder('Oro\Bundle\IssueBundle\Placeholder\PlaceholderFilter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = new IsParentValidator($this->placeholderFilter);
    }

    /**
     * @dataProvider getInvalidData
     * @expectedException \InvalidArgumentException
     *
     * @param mixed $value
     */
    public function testInvalidArgument($value)
    {
        /** @var Constraint $constraint */
        $constraint = $this->constraint;
        $this->validator->validate($value, $constraint);
    }

    public function testValidateWhenValidValue()
    {
        $context = $this->getMockBuilder('Symfony\Component\Validator\Context\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
        $value = $this->getMock('Oro\Bundle\IssueBundle\Entity\Issue');

        $this->placeholderFilter
            ->expects($this->once())
            ->method('isAddChildApplicable')
            ->willReturn(true);

        $context->expects($this->never())
            ->method('buildViolation');
       /** @var Constraint $constraint */
        $constraint = $this->constraint;

        $this->validator->initialize($context);
        $this->validator->validate($value, $constraint);
    }

    public function testValidateWhenInvalidValue()
    {
        $value = $this->getMock('Oro\Bundle\IssueBundle\Entity\Issue');

        $this->placeholderFilter
            ->expects($this->once())
            ->method('isAddChildApplicable')
            ->willReturn(false);

        $violationBuilder = $this->getMock('Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface');
        $violationBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->willReturn($violationBuilder);
        $violationBuilder
            ->expects($this->once())
            ->method('addViolation');

        $context = $this->getMockBuilder('Symfony\Component\Validator\Context\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        /** @var Constraint $constraint */
        $constraint = $this->constraint;

        $this->validator->initialize($context);
        $this->validator->validate($value, $constraint);
    }

    public function getInvalidData()
    {
        $callback = function () {
            return 0;
        };

        return [
            'string'    => ['string'],
            'bool'      => [false],
            'null'      => [null],
            'integer'   => [0],
            'array'     => [range(1, 5)],
            'object'    => [new \stdClass()],
            'callback'  => [$callback]
        ];
    }
}
