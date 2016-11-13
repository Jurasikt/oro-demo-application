<?php

namespace Oro\Bundle\IssueBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\IssueBundle\Tests\Functional\Controller\Api\Rest\DataFixtures\ApiKeys;

/**
 * @dbIsolation
 */
class IssueControllerACLTest extends WebTestCase
{

    protected static $issue;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateWsseAuthHeader(ApiKeys::USER_NAME, ApiKeys::USER_PASSWORD)
        );

        $this->loadFixtures(
            [
                'Oro\Bundle\IssueBundle\Tests\Functional\Controller\Api\Rest\DataFixtures\LoadIssueData',
                'Oro\Bundle\IssueBundle\Tests\Functional\Controller\Api\Rest\DataFixtures\LoadUserData'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function postFixtureLoad()
    {
        $issue = $this->client->getContainer()->get('doctrine')
            ->getRepository('OroIssueBundle:Issue')->findOneBy(['summary' => 'ACL test']);

        self::$issue = $issue->getId();
    }

    public function testCget()
    {
        $this->clientWsseRequest('GET', $this->getUrl('oro_api_get_issues'));
        $this->getJsonResponseContent($this->client->getResponse(), 403);
    }

    public function testGet()
    {
        $this->clientWsseRequest('GET', $this->getUrl('oro_api_get_issue', ['id' => self::$issue]));
        $this->getJsonResponseContent($this->client->getResponse(), 403);
    }

    public function testCreate()
    {
        $this->clientWsseRequest('POST', $this->getUrl('oro_api_post_issue'), $this->getIssue());
        $this->getJsonResponseContent($this->client->getResponse(), 403);
    }

    public function testUpdate()
    {
        $this->clientWsseRequest('PUT', $this->getUrl('oro_api_put_issue', ['id' => self::$issue]), $this->getIssue());
        $this->getJsonResponseContent($this->client->getResponse(), 403);
    }

    public function testDelete()
    {
        $this->clientWsseRequest('DELETE', $this->getUrl('oro_api_delete_issue', ['id' => self::$issue]));
        $this->getJsonResponseContent($this->client->getResponse(), 403);
    }

    protected function getIssue($type = 'story')
    {
        return [
            'summary' => 'test',
            'issuePriority' => 'major',
            'issueType' => $type
        ];
    }

    private function clientWsseRequest(
        $method,
        $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        $content = null,
        $changeHistory = true
    ) {
        $server = array_merge($server, $this->generateWsseAuthHeader(ApiKeys::USER_NAME, ApiKeys::USER_PASSWORD));

        return $this->client->request(
            $method,
            $uri,
            $parameters,
            $files,
            $server,
            $content,
            $changeHistory
        );
    }
}
