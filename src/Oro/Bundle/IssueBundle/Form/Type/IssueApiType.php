<?php

namespace Oro\Bundle\IssueBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\IssueBundle\Validator\Constraints as OroIssueAssert;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class IssueApiType extends IssueType
{
    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    public function setSecurityFacade(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Oro\Bundle\IssueBundle\Entity\Issue',
                'cascade_validation' => true,
                'csrf_protection' => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_issue_api';
    }

    /**
     * {@inheritdoc}
     */
    public function postSetData(FormEvent $event)
    {
        $event->getForm()
            ->add(
                'issueType',
                'choice',
                [
                    'constraints' => [new OroIssueAssert\IssueType(), new Assert\NotNull()],
                    'choices' => $this->getAllowIssueType(),
                    'label' => 'Type',
                    'required' => true
                ]
            );
    }

    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $data['reporter'] = $this->securityFacade->getLoggedUser();

        if (array_key_exists('issueType', $data) && $data['issueType'] == Issue::ISSUE_TYPE_SUBTASK) {
            $form->add(
                'parent',
                'entity',
                [
                    'class' => 'Oro\Bundle\IssueBundle\Entity\Issue',
                    'constraints' => [new Assert\NotNull(), new OroIssueAssert\IsParent()]
                ]
            );
        }
        $event->setData($data);
    }

    protected function getAllowIssueType()
    {
        return [
            Issue::ISSUE_TYPE_TASK => 'Task',
            Issue::ISSUE_TYPE_BUG  => 'Bug',
            Issue::ISSUE_TYPE_STORY => 'Story',
            Issue::ISSUE_TYPE_SUBTASK => 'Subtask'
        ];
    }
}
