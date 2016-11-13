<?php

namespace Oro\Bundle\IssueBundle\Placeholder;

use Oro\Bundle\IssueBundle\Entity\Issue;

class PlaceholderFilter
{
    /**
     * @param Issue $issue
     * @return bool
     */
    public function isAddChildApplicable(Issue $issue)
    {
        return in_array($issue->getIssueType(), [Issue::ISSUE_TYPE_STORY]);
    }
}
