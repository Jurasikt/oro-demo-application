<?php

namespace Oro\Bundle\IssueBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class DashboardController extends Controller
{
    /**
     * @Route("/issue/chart", name="oro_issue_dashboard_chart")
     * @AclAncestor("oro_issue_view")
     * @Template("OroIssueBundle:Dashboard:issueStatusChart.html.twig")
     */
    public function issuesStatusAction()
    {
        $items = $this->get('oro_issue.issue_data_provider')->getIssueCountGroupByStatus();
        $widgetAttr = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig('issue_status');

        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($items)
            ->setOptions(
                [
                    'name'        => 'bar_chart',
                    'data_schema' => [
                        'label' => ['field_name' => 'label'],
                        'value' => ['field_name' => 'number']
                    ]
                ]
            )
            ->getView();

        return $widgetAttr;
    }

    /**
     * @Route("/issue/grid", name="oro_issue_dashboard_grid")
     * @AclAncestor("oro_issue_view")
     * @Template("OroIssueBundle:Dashboard:activityGrid.html.twig")
     */
    public function issuesGridAction()
    {
        return  $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig('issue_grid');
    }
}
