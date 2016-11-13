<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Form;

use Symfony\Component\Form\FormEvents;

use Oro\Bundle\IssueBundle\Form\Type\IssueType;

class IssueTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IssueType
     */
    protected $formType;

    public function setUp()
    {
        $this->formType = new IssueType();
    }

    public function testGetName()
    {
        $this->assertSame($this->formType->getName(), 'oro_issue');
    }

    /**
     * @dataProvider postSetDataProvider
     *
     * @param $type
     * @param $data
     */
    public function testPostSetData($type, $data)
    {
        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->once())
            ->method('add')
            ->with(
                $this->equalTo('issueType'),
                $this->equalTo($type),
                $this->isType('array')
            );

        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->willReturn($data);
        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $this->formType->postSetData($event);
    }

    public function testBuildForm()
    {
        $formBuilder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $formBuilder->expects($this->exactly(5))
            ->method('add')
            ->willReturn($formBuilder);
        $formBuilder->expects($this->once())
            ->method('addEventListener')
            ->with(
                FormEvents::POST_SET_DATA,
                $this->isType('array')
            );

        $this->formType->buildForm($formBuilder, []);
    }

    public function postSetDataProvider()
    {
        $issue = $this->getMock('Oro\Bundle\IssueBundle\Entity\Issue');

        $issueWhatHasParent = clone $issue;
        $issueWhatHasParent->expects($this->any())
            ->method('getParent')
            ->willReturn($issue);

        $issueWhatNotHasParent = clone $issue;
        $issueWhatNotHasParent->expects($this->once())
            ->method('getParent')
            ->willReturn(null);

        return [
            [
                'type'    => 'choice',
                'data'    => $issueWhatNotHasParent,
            ],
            [
                'type'    => 'hidden',
                'data'    => $issueWhatHasParent
            ]
        ];
    }
}
