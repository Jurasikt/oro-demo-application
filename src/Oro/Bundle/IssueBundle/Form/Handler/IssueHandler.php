<?php

namespace Oro\Bundle\IssueBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\IssueBundle\Entity\Issue;

class IssueHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $manager
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  Issue  $entity
     * @return bool True on successful processing, false otherwise
     */
    public function process(Issue $entity)
    {
        $this->form->setData($entity);

        if ($entity->getReporter()) {
            FormUtils::replaceField($this->form, 'reporter', ['read_only' => true]);
        }

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            if ($this->request->getMethod() == 'POST') {
                $this->form->handleRequest($this->request);
            } else {
                $this->form->submit($this->request);
            }

            if ($this->form->isSubmitted() && $this->form->isValid()) {
                $entity = $this->form->getData();
                $this->manager->persist($entity);
                $this->manager->flush();
                return true;
            }
        }
        return false;
    }

    /**
     * @param FormInterface $form
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
