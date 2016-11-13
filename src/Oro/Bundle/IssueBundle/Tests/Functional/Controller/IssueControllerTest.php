<?php

namespace Oro\Bundle\IssueBundle\Tests\Functional\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\IssueBundle\Entity\Issue;

/**
 * @dbIsolation
 */
class IssueControllerTest extends WebTestCase
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var string
     */
    private static $issueSummary;

    public function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->registry = $this->client->getContainer()->get('doctrine');
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_issue_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    /**
     * @dataProvider issueFormDataProvider
     * @depends testIndex
     */
    public function testValidateIssueForm($formData, $isValid)
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_issue_create'));
        $form = $crawler->selectButton('Save and Close')->form();
        $this->client->followRedirects(true);

        foreach ($formData as $field => $value) {
            $form[$field] = $value;
        }

        $crawler = $this->client->submit($form);
        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);

        if ($isValid) {
            $this->assertContains("Issue saved", $crawler->html());
        } else {
            $this->assertNotContains("Issue saved", $crawler->html());
        }
    }

    /**
     * @depends testValidateIssueForm
     */
    public function testCreated()
    {
        $this->assertInstanceOf('Oro\Bundle\IssueBundle\Entity\Issue', $this->getIssueBySummary());
    }

    /**
     * @depends testCreated
     */
    public function testUpdate()
    {
        $response = $this->client->requestGrid(
            'oro-issue-grid',
            ['oro-issue-grid[_filter][summary][value]' => self::$issueSummary]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $crawler = $this->client->request('GET', $this->getUrl('oro_issue_update', ['id' => $result['id']]));

        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_issue[description]'] = 'testUpdateIssue';
        $this->client->followRedirects(true);
        $this->client->submit($form);

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);
        $this->assertSame('testUpdateIssue', $this->getIssueBySummary()->getDescription());
    }

    /**
     * @depends testCreated
     */
    public function testAddCollaborator()
    {
        $user = $this->client->getContainer()->get('oro_security.security_facade')->getLoggedUser();
        $this->assertTrue($this->getIssueBySummary()->containsCollaborator($user));
    }

    /**
     * @depends testCreated
     */
    public function testIssueOrganization()
    {
        $issue = $this->getIssueBySummary();
        $this->assertInstanceOf(
            'Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface',
            $issue->getOrganization()
        );
    }

    /**
     * @depends testCreated
     */
    public function testAddSubtask()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_issue_child_create', ['id' => $this->getIssueBySummary()->getId()])
        );

        $summary = uniqid('subtask_test');

        $form = $crawler->selectButton('Save and Close')->form();
        $this->client->followRedirects(true);
        $form['oro_issue[summary]'] = $summary;
        $form['oro_issue[issuePriority]'] = 'major';
        $this->client->submit($form);

        $subtask = $this->getIssueBySummary($summary);
        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), 200);
        $this->assertInstanceOf('Oro\Bundle\IssueBundle\Entity\Issue', $subtask);
        return $subtask;
    }

    /**
     * @depends testCreated
     */
    public function testViewIssue()
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_issue_view', ['id' => $this->getIssueBySummary()->getId()])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains(self::$issueSummary, $result->getContent());
    }

    public function testUserDatagridWidget()
    {
        $id = $this->client->getContainer()
            ->get('oro_security.security_facade')->getLoggedUserId();
        $this->client->request('GET', $this->getUrl('oro_issue_user_datagrid', ['id' => $id]));
        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($response, 200);
        $this->assertContains('widget-content', $response->getContent());
    }

    /**
     * @depends testAddSubtask
     *
     * @param Issue $subtask
     */
    public function testSubtaskWidget(Issue $subtask)
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_issue_user_datagrid',
                [
                    'id' => $subtask->getParent()->getId()
                ]
            )
        );

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($response, 200);
        $this->assertContains('widget-content', $response->getContent());
    }

    public function issueFormDataProvider()
    {
        self::$issueSummary = uniqid('controller_test');
        return [
            'summaryEmpty' => [
                ['oro_issue[summary]' => '', 'oro_issue[issuePriority]' => 'major',
                    'oro_issue[issueType]' => 'bug'],
                false
            ],
            'valid' => [
                ['oro_issue[summary]' => self::$issueSummary, 'oro_issue[issuePriority]' => 'major',
                    'oro_issue[issueType]' => 'story', 'oro_issue[assignee]' => 1],
                true
            ]
        ];
    }

    /**
     * @param string $summary
     * @return Issue
     */
    private function getIssueBySummary($summary = null)
    {
        if (null === $summary) {
            $summary = self::$issueSummary;
        }

        return $this->registry->getRepository('OroIssueBundle:Issue')->findOneBy(['summary' => $summary]);
    }
}
