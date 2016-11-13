<?php

namespace Oro\Bundle\IssueBundle\Tests\Placeholder;

use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\IssueBundle\Placeholder\PlaceholderFilter;

class PlaceholderFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getIssues
     *
     * @param $issue
     * @param  $isApplicable
     */
    public function testAddChildApplicable($issue, $isApplicable)
    {
        $filter = new PlaceholderFilter();
        $this->assertSame($filter->isAddChildApplicable($issue), $isApplicable);
    }

    public function getIssues()
    {
        $dataForTestApplicable = [
            [Issue::ISSUE_TYPE_BUG, false],
            [Issue::ISSUE_TYPE_STORY, true],
            [Issue::ISSUE_TYPE_SUBTASK, false],
            [Issue::ISSUE_TYPE_TASK, false]
        ];

        foreach ($dataForTestApplicable as &$item) {
            $issue = $this->getMock('Oro\Bundle\IssueBundle\Entity\Issue');
            $issue->expects($this->once())->method('getIssueType')->willReturn($item[0]);
            $item[0] = $issue;
        }
        return $dataForTestApplicable;
    }
}
