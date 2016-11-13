<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Form;

use Symfony\Component\Form\FormEvents;

use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\IssueBundle\Form\Type\IssueApiType;

class IssueApiTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IssueApiType
     */
    protected $formType;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $securityFacade;

    public function setUp()
    {
        $this->formType = new IssueApiType();
        $this->securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()->getMock();
        $this->formType->setSecurityFacade($this->securityFacade);
    }

    public function testBuildForm()
    {
        $formBuilder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $formBuilder->expects($this->exactly(5))
            ->method('add')
            ->willReturn($formBuilder);
        $formBuilder->expects($this->exactly(2))
            ->method('addEventListener')
            ->withConsecutive(
                [FormEvents::POST_SET_DATA, $this->isType('array')],
                [FormEvents::PRE_SUBMIT, $this->isType('array')]
            );

        $this->formType->buildForm($formBuilder, []);
    }

    public function testGetName()
    {
        $this->assertSame($this->formType->getName(), 'oro_issue_api');
    }

    public function testPostSetData()
    {
        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->once())
            ->method('add')
            ->with(
                'issueType',
                'choice',
                $this->isType('array')
            );

        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $this->formType->postSetData($event);
    }

    /**
     * @dataProvider preSubmitDataProvider
     *
     * @param $data
     * @param $expects
     */
    public function testPreSubmit($data, $expects)
    {
        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $form->expects($expects)
            ->method('add')
            ->with(
                'parent',
                'entity',
                $this->isType('array')
            );

        $this->securityFacade->expects($this->once())
            ->method('getLoggedUser');

        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);
        $event->expects($this->once())
            ->method('getData')
            ->willReturn($data);

        $this->formType->preSubmit($event);
    }

    public function preSubmitDataProvider()
    {
        return [
            'randomData' => [
                'data' => [],
                'expects' => $this->never()
            ],
            'formDataForTask' => [
                'data' => ['issueType' => Issue::ISSUE_TYPE_TASK],
                'expects' => $this->never()
            ],
            'formDataForStory' => [
                'data' => ['issueType' => Issue::ISSUE_TYPE_STORY],
                'expects' => $this->never()
            ],
            'formDataForBug' => [
                'data' => ['issueType' => Issue::ISSUE_TYPE_BUG],
                'expects' => $this->never()
            ],
            'formDataForSubtask' => [
                'data' => ['issueType' => Issue::ISSUE_TYPE_SUBTASK],
                'expects' => $this->once()
            ]
        ];
    }
}
