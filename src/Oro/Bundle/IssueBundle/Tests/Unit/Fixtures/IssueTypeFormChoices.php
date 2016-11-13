<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Fixtures;

use Oro\Bundle\IssueBundle\Entity\Issue;

class IssueTypeFormChoices
{
    /**
     * @param string $type
     * @return array
     */
    public function getIssueTypeChoice($type = null)
    {
        if ($type == Issue::ISSUE_TYPE_STORY) {
            return [Issue::ISSUE_TYPE_SUBTASK => 'Subtask'];
        }

        return [
            Issue::ISSUE_TYPE_TASK => 'Task',
            Issue::ISSUE_TYPE_BUG  => 'Bug',
            Issue::ISSUE_TYPE_STORY => 'Story'
        ];
    }
}
