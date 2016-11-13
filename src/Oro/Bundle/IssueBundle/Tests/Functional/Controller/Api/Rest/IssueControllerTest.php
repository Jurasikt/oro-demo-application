<?php

namespace Oro\Bundle\IssueBundle\Tests\Functional\Controller\Api\Rest;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class IssueControllerTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function testCget()
    {
        $this->client->request('GET', $this->getUrl('oro_api_get_issues'));
        $this->getJsonResponseContent($this->client->getResponse(), 200);
    }

    public function testCreate()
    {
        $issue = $this->getIssue();

        $this->client->request('POST', $this->getUrl('oro_api_post_issue'), $issue);
        $issue = $this->getJsonResponseContent($this->client->getResponse(), 201);
        return $issue['id'];
    }

    /**
     * @depends testCreate
     *
     * @param $id
     */
    public function testGet($id)
    {
        $this->client->request('GET', $this->getUrl('oro_api_get_issue', ['id' => $id]));
        $issue = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertSame('test', $issue['summary']);
    }

    /**
     * @depends testCreate
     *
     * @param $id
     */
    public function testPut($id)
    {
        $issue = $this->getIssue();
        $issue['summary'] = 'update test';

        $this->client->request('PUT', $this->getUrl('oro_api_put_issue', ['id' => $id]), $issue);
        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 204);
    }

    /**
     * @depends testCreate
     *
     * @param $id
     */
    public function testSubtaskCreate($id)
    {
        $subtask = array_merge($this->getIssue('subtask'), ['parent' => $id]);

        $this->client->request('POST', $this->getUrl('oro_api_post_issue'), $subtask);
        $issue = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $subtask = $this->entityManager->find('OroIssueBundle:Issue', $issue['id']);
        $this->assertInstanceOf('Oro\Bundle\IssueBundle\Entity\Issue', $subtask);

        return $issue['id'];
    }

    /**
     * @depends testCreate
     *
     * @param $id
     */
    public function testUpdateIssueTypeIfChildExist($id)
    {
        $issue = $this->getIssue('task');

        $this->client->request('PUT', $this->getUrl('oro_api_put_issue', ['id' => $id]), $issue);
        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 400);
    }

    /**
     * @depends testCreate
     *
     * @param $id
     */
    public function testDelete($id)
    {
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_issue', ['id' => $id]));
        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 204);
    }

    /**
     * @depends testSubtaskCreate
     *
     * @param $id
     */
    public function testCascadeDelete($id)
    {
        $subtask = $this->entityManager->find('OroIssueBundle:Issue', $id);
        $this->assertNull($subtask);
    }

    /**
     * @depends testCreate
     */
    public function testSubtaskCreateIfParentNotStory()
    {
        $issue = $this->getIssue('task');

        $this->client->request('POST', $this->getUrl('oro_api_post_issue'), $issue);
        $issue = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $subtask = array_merge($this->getIssue('subtask'), ['parent' => $issue['id']]);
        $this->client->request('POST', $this->getUrl('oro_api_post_issue'), $subtask);
        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), 400);
    }

    protected function getIssue($type = 'story')
    {
        return [
            'summary' => 'test',
            'issuePriority' => 'major',
            'issueType' => $type
        ];
    }
}
