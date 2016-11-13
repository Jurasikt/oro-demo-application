<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Validator;

use Oro\Bundle\IssueBundle\Validator\Constraints\IssueType;

class IssueTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IssueType
     */
    protected $constraint;

    public function setUp()
    {
        $this->constraint = new IssueType();
    }

    public function testValidatedBy()
    {
        $this->assertSame($this->constraint->validatedBy(), 'oro_issue.validator.issue_type');
    }
}
