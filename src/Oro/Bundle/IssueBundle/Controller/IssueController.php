<?php

namespace Oro\Bundle\IssueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\IssueBundle\Entity\Issue;

/**
 * @Route("/issue")
 */
class IssueController extends Controller
{
    /**
     * @Route("/datagrid/{grid}", name="oro_issue_index", defaults={"grid"="oro-issue-grid"})
     * @Acl(
     *      id="oro_issue_view",
     *      type="entity",
     *      class="OroIssueBundle:Issue",
     *      permission="VIEW"
     * )
     * @Template()
     */
    public function indexAction($grid)
    {
        return [
            'entity_class' => $this->container->getParameter('oro_issue.entity.class'),
            'gridName' => $grid
        ];
    }

    /**
     * @Route("/view/{id}", name="oro_issue_view", requirements={"id"="\d+"})
     * @AclAncestor("oro_issue_view")
     * @Template()
     */
    public function viewAction(Issue $issue)
    {
        return [
            'entity' => $issue
        ];
    }

    /**
     * @Route("/create", name="oro_issue_create")
     * @Acl(
     *      id="oro_issue_create",
     *      type="entity",
     *      class="OroIssueBundle:Issue",
     *      permission="CREATE"
     * )
     * @Template("OroIssueBundle:Issue:update.html.twig")
     */
    public function createIssueAction()
    {
        $issue = new Issue();
        $loggedUser = $this->get('oro_security.security_facade')->getLoggedUser();
        $issue->setReporter($loggedUser);

        if ($this->getRequest()->get('_widgetContainer') && $this->getRequest()->getMethod() == 'GET') {
            $issue->setAssignee($loggedUser);
        }

        $handler = $this->container->get('oro_issue.form.handler');
        $saved = $handler->process($issue);

        if ($saved && !$this->getRequest()->get('_widgetContainer')) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.issue.saved_message')
            );
            return $this->redirect($this->generateUrl('oro_issue_index'));
        }

        return [
            'saved'  => $saved,
            'entity' => $issue,
            'form' => $handler->getForm()->createView(),
            'formAction' => $this->generateUrl('oro_issue_create')
        ];
    }

    /**
     * @Route("/child/create/{id}", name="oro_issue_child_create", requirements={"id"="\d+"})
     * @AclAncestor("oro_issue_create")
     * @Template("OroIssueBundle:Issue:update.html.twig")
     */
    public function createChildAction(Issue $parent)
    {
        if (! $this->get('oro_issue.placeholder.filter')->isAddChildApplicable($parent)) {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('oro.issue.controller.add_subtask_error')
            );
            return $this->redirect($this->generateUrl('oro_issue_index'));
        }

        $issue = new Issue();
        $issue->setParent($parent);

        $handler = $this->container->get('oro_issue.form.handler');

        if ($handler->process($issue)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.issue.saved_message')
            );
            return $this->redirect($this->generateUrl('oro_issue_index'));
        }

        return [
            'entity' => $issue,
            'form' => $handler->getForm()->createView(),
            'formAction' => $this->generateUrl('oro_issue_child_create', ['id' => $parent->getId()])
        ];
    }

    /**
     * @Route("/update/{id}", name="oro_issue_update", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_issue_update",
     *      type="entity",
     *      class="OroIssueBundle:Issue",
     *      permission="EDIT"
     * )
     * @Template()
     */
    public function updateAction(Issue $issue)
    {
        $handler = $this->container->get('oro_issue.form.handler');

        if ($handler->process($issue)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.issue.saved_message')
            );
            return $this->redirect($this->generateUrl('oro_issue_view', ['id' => $issue->getId()]));
        }

        return [
            'entity' => $issue,
            'form' => $handler->getForm()->createView(),
            'formAction' => $this->generateUrl('oro_issue_update', ['id' => $issue->getId()]),
        ];
    }

    /**
     * @Route("/user/{id}", name="oro_issue_user_datagrid", requirements={"id"="\d+"})
     * @AclAncestor("oro_issue_view")
     * @Template()
     */
    public function userDatagridAction($id)
    {
        return ['userId' => $id];
    }

    /**
     * @Route("/widget/subtask/{parent}", name="oro_widget_subtask_view", requirements={"parent"="\d+"})
     * @AclAncestor("oro_issue_view")
     * @Template()
     */
    public function subtaskAction($parent)
    {
        return ['parent' => $parent];
    }
}
