<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Validator;

use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\IssueBundle\Validator\Constraints\IssueTypeValidator;
use Symfony\Component\Validator\Constraint;

class IssueTypeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IssueTypeValidator
     */
    protected $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $issueManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $constraint;

    public function setUp()
    {
        $this->constraint = $this->getMockBuilder('Symfony\Component\Validator\Constraint')
            ->disableOriginalConstructor()
            ->getMock();

        $this->issueManager = $this->getMockBuilder('Oro\Bundle\IssueBundle\Entity\Manager\IssueManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = new IssueTypeValidator($this->issueManager);
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

        $context = $this->getMockBuilder('Symfony\Component\Validator\Context\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();

        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->once())->method('getData')->willReturn($value);

        $context->expects($this->once())
            ->method('getRoot')
            ->willReturn($form);
        $this->validator->initialize($context);

        $this->validator->validate(Issue::ISSUE_TYPE_SUBTASK, $constraint);
    }

    /**
     * @dataProvider issueTypeProvider
     *
     * @param $issueType
     * @param $valid
     */
    public function testValidate($issueType, $valid)
    {
        $context = $this->getMockBuilder('Symfony\Component\Validator\Context\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();

        $value = $this->getMock('Oro\Bundle\IssueBundle\Entity\Issue');
        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->once())->method('getData')->willReturn($value);

        $context->expects($this->once())
            ->method('getRoot')
            ->willReturn($form);

        if ($valid) {
            $context->expects($this->never())
                ->method('buildViolation');
        } else {
            $violationBuilder = $this
                ->getMock('Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface');
            $violationBuilder
                ->expects($this->once())
                ->method('setParameter')
                ->willReturn($violationBuilder);
            $violationBuilder
                ->expects($this->once())
                ->method('addViolation');

            $context->expects($this->once())
                ->method('buildViolation')
                ->willReturn($violationBuilder);

            $this->issueManager->expects($this->once())->method('getChild')
                ->willReturn(['test']);
        }

        /** @var Constraint $constraint */
        $constraint = $this->constraint;

        $this->validator->initialize($context);
        $this->validator->validate($issueType, $constraint);
    }

    public function issueTypeProvider()
    {
        return [
            [
                'issueType' => Issue::ISSUE_TYPE_SUBTASK,
                'valid'     => false
            ],
            [
                'issueType' => Issue::ISSUE_TYPE_STORY,
                'valid'     => true
            ]
        ];
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
