<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Validator;

use Oro\Bundle\IssueBundle\Validator\Constraints\IsParent;

class IsParentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IsParent
     */
    protected $constraint;

    public function setUp()
    {
        $this->constraint = new IsParent();
    }

    public function testValidatedBy()
    {
        $this->assertSame($this->constraint->validatedBy(), 'oro_issue.validator.is_parent');
    }
}
