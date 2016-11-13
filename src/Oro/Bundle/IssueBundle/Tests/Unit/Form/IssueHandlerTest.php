<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Form;

use Oro\Bundle\IssueBundle\Form\Handler\IssueHandler;

class IssueHandlerTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()
            ->getMock();
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');

        $handler = new IssueHandler($requestStack, $em);
        $handlerRequest = (new \ReflectionClass($handler))
            ->getProperty('request');
        $handlerRequest->setAccessible(true);

        $this->assertSame($handlerRequest->getValue($handler), $request);
    }

    /**
     * @depends testConstructor
     */
    public function testGetSetForm()
    {
        $requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()
            ->getMock();

        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $form = $this->getMock('Symfony\Component\Form\FormInterface');

        $handler = new IssueHandler($requestStack, $em);
        $handler->setForm($form);

        $this->assertSame($form, $handler->getForm());
    }

    /**
     * @dataProvider processProvider
     * @depends testConstructor
     *
     * @param $form
     * @param $requestStack
     * @param $isValid
     */
    public function testProcess($form, $requestStack, $isValid)
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');

        if ($isValid) {
            $em->expects($this->once())->method('flush');
        } else {
            $em->expects($this->never())->method('flush');
        }

        $handler = new IssueHandler($requestStack, $em);
        $handler->setForm($form);
        $this->assertSame(
            $handler->process($this->getMock('Oro\Bundle\IssueBundle\Entity\Issue')),
            $isValid
        );
    }

    public function processProvider()
    {
        return [
            [
                'form' => $this->buildForm(true, true),
                'requestStack' => $this->buildRequest('GET'),
                'isValid' => false,
            ],
            [
                'form' => $this->buildForm(true, true),
                'requestStack' => $this->buildRequest('DELETE'),
                'isValid' => false,
            ],
            [
                'form' => $this->buildForm(true, true),
                'requestStack' => $this->buildRequest('OPTIONS'),
                'isValid' => false,
            ],
            [
                'form' => $this->buildForm(false, false),
                'requestStack' => $this->buildRequest('PUT'),
                'isValid' => false,
            ],
            [
                'form' => $this->buildForm(true, false),
                'requestStack' => $this->buildRequest('PUT'),
                'isValid' => false,
            ],
            [
                'form' => $this->buildForm(false, true),
                'requestStack' => $this->buildRequest('PUT'),
                'isValid' => false,
            ],
            [
                'form' => $this->buildForm(true, true),
                'requestStack' => $this->buildRequest('PUT'),
                'isValid' => true,
            ],
            [
                'form' => $this->buildForm(false, false),
                'requestStack' => $this->buildRequest('POST'),
                'isValid' => false,
            ],
            [
                'form' => $this->buildForm(true, false),
                'requestStack' => $this->buildRequest('POST'),
                'isValid' => false,
            ],
            [
                'form' => $this->buildForm(false, true),
                'requestStack' => $this->buildRequest('POST'),
                'isValid' => false,
            ],
            [
                'form' => $this->buildForm(true, true),
                'requestStack' => $this->buildRequest('POST'),
                'isValid' => true,
            ]
        ];
    }

    private function buildRequest($method)
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->method('getMethod')->willReturn($method);

        $requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()
            ->getMock();
        $requestStack->method('getCurrentRequest')->willReturn($request);
        return $requestStack;
    }

    private function buildForm($valid, $submitted)
    {
        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $form->method('isSubmitted')->willReturn($submitted);
        $form->method('isValid')->willReturn($valid);
        return $form;
    }
}
