<?php

namespace Oro\Bundle\IssueBundle\Form\Type;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\IssueBundle\Validator\Constraints as OroIssueAssert;

class IssueType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'summary',
                'text',
                [
                    'required' => true,
                    'label' => 'oro.issue.summary.label'
                ]
            )
            ->add(
                'description',
                'oro_resizeable_rich_text',
                [
                    'required' => false,
                    'label' => 'oro.issue.description.label'
                ]
            )
            ->add(
                'assignee',
                'oro_user_select',
                [
                    'label' => 'oro.issue.assignee.label'
                ]
            )
            ->add(
                'issuePriority',
                'translatable_entity',
                [
                    'label' => 'oro.issue.issue_priority.label',
                    'class' => 'Oro\Bundle\IssueBundle\Entity\IssuePriority',
                    'required' => true,
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->createQueryBuilder('ip')->orderBy('ip.order');
                    }
                ]
            )
            ->add(
                'issueResolution',
                'translatable_entity',
                [
                    'label' => 'oro.issue.issue_resolution.label',
                    'class' => 'Oro\Bundle\IssueBundle\Entity\IssueResolution',
                    'required' => false,
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->createQueryBuilder('ir')->orderBy('ir.order');
                    }
                ]
            )
            ->addEventListener(FormEvents::POST_SET_DATA, [$this, 'postSetData']);
    }

    /**
     * Post set data handler
     *
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        $issue = $event->getData();
        $form = $event->getForm();

        if ($issue instanceof Issue && !$issue->getParent()) {
            $form->add(
                'issueType',
                'choice',
                [
                    'constraints' => [new OroIssueAssert\IssueType(), new Assert\NotNull()],
                    'choices' => [
                        Issue::ISSUE_TYPE_TASK => 'Task',
                        Issue::ISSUE_TYPE_BUG  => 'Bug',
                        Issue::ISSUE_TYPE_STORY => 'Story'
                    ],
                    'label' => 'oro.issue.issue_type.label',
                    'required' => true
                ]
            );
        } else {
            $form->add(
                'issueType',
                'hidden',
                [
                    'constraints' => [new OroIssueAssert\IssueType(), new Assert\NotNull()],
                    'data' => Issue::ISSUE_TYPE_SUBTASK
                ]
            );
        }
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
                'intention' => 'issue',
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
        return 'oro_issue';
    }
}
