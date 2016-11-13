<?php

namespace Oro\Bundle\IssueBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\UserBundle\Entity\User;

use Oro\Bundle\IssueBundle\Entity\Issue;

/**
 * @RouteResource("issue")
 * @NamePrefix("oro_api_")
 */
class IssueController extends RestController
{

    /**
     * REST GET list
     *
     * @QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *      description="Get all issue items",
     *      resource=true
     * )
     * @AclAncestor("oro_issue_view")
     * @return Response
     */
    public function cgetAction()
    {
        $page  = (int)$this->getCurrentRequest()->get('page', 1);
        $limit = (int)$this->getCurrentRequest()->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get Issue item",
     *      resource=true
     * )
     * @AclAncestor("oro_issue_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id Issue item id
     *
     * @ApiDoc(
     *      description="Update Issue",
     *      resource=true
     * )
     * @AclAncestor("oro_issue_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new Issue
     *
     * @ApiDoc(
     *      description="Create new Issue",
     *      resource=true
     * )
     * @AclAncestor("oro_issue_create")
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete Issue",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_issue_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroIssueBundle:Issue"
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function transformEntityField($field, &$value)
    {
        switch ($field) {
            case 'reporter':
            case 'assignee':
                if ($value instanceof User) {
                    $value = $value->getId();
                }
                break;
            case 'parent':
                if ($value instanceof Issue) {
                    $value = $value->getId();
                }
                break;
            default:
                parent::transformEntityField($field, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->container->get('oro_issue.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('oro_issue.form.handler.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_issue.form.api');
    }

    /**
     * @return Request
     */
    private function getCurrentRequest()
    {
        return $this->get('request_stack')->getCurrentRequest();
    }
}
