<?php

namespace Oro\Bundle\IssueBundle\Tests\Functional\Controller\Dashboard;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class DashboardControllerTest extends WebTestCase
{

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
    }

    /**
     * @dataProvider widgetDataProvider
     *
     * @param $route
     * @param $widget
     */
    public function testDashboardWidget($route, $widget)
    {
        $widgetAttr = $this->client->getContainer()
            ->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);

        $this->client->request('GET', $this->getUrl($route));
        $response = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($response, 200);
        $this->assertContains($widgetAttr['widgetLabel'], $response->getContent());
    }

    public function widgetDataProvider()
    {
        return [
            'issuesStatusChart' => [
                'routeName' => 'oro_issue_dashboard_chart',
                'widget'    => 'issue_status'
            ],
            'issuesGrid' => [
                'routeName' => 'oro_issue_dashboard_grid',
                'widget'    => 'issue_grid'
            ]
        ];
    }
}
