<?php

namespace Oro\Bundle\IssueBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\CommentBundle\Migration\Extension\CommentExtension;
use Oro\Bundle\CommentBundle\Migration\Extension\CommentExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtension;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtensionAwareInterface;

class OroIssueBundle implements
    Migration,
    ExtendExtensionAwareInterface,
    CommentExtensionAwareInterface,
    NoteExtensionAwareInterface,
    ActivityExtensionAwareInterface
{
    const ISSUE_TABLE = 'oro_issue';
    const ISSUE_PRIORITY_TABLE = 'oro_issue_priority';
    const ISSUE_RESOLUTION_TABLE = 'oro_issue_resolution';
    const ISSUE_COLLABORATOR_TABLE = 'oro_issue_collaborator';

    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * @var CommentExtension
     */
    protected $comment;

    /**
     * @var NoteExtension
     */
    protected $noteExtension;

    /** @var ActivityExtension */
    protected $activityExtension;

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * @param CommentExtension $commentExtension
     */
    public function setCommentExtension(CommentExtension $commentExtension)
    {
        $this->comment = $commentExtension;
    }

    /**
     * @inheritdoc
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * @inheritdoc
     */
    public function setNoteExtension(NoteExtension $noteExtension)
    {
        $this->noteExtension = $noteExtension;
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::oroIssuePriority($schema);
        self::oroIssueResolution($schema);
        self::oroIssueTable($schema);
        self::addCollaborators($schema);
        self::addComment($schema, $this->comment);
        self::addNoteAssociation($schema, $this->noteExtension);
        self::addForeignKeysForIssue($schema);
        self::addAddForeignKeysForCollaborator($schema);
        self::addActivityAssociation($schema, $this->activityExtension);
    }

    /**
     * Generate table oro_issue
     *
     * @param Schema $schema
     */
    protected static function oroIssueTable(Schema $schema)
    {
        if ($schema->hasTable(self::ISSUE_TABLE)) {
            $schema->dropTable(self::ISSUE_TABLE);
        }
        $table = $schema->createTable(self::ISSUE_TABLE);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('issue_type', 'string', ['length' => 255]);
        $table->addColumn('reporter_id', 'integer');
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('assignee_id', 'integer', ['notnull' => false]);
        $table->addColumn('summary', 'string', ['length' => 255]);
        $table->addColumn('code', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->addColumn('parent_id', 'integer', ['notnull' => false]);
        $table->addColumn('issue_resolution_id', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('issue_priority_id', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['workflow_item_id']);
        $table->addUniqueIndex(['code']);
    }

    /**
     * Generate table oro_issue_priority
     *
     * @param Schema $schema
     */
    protected static function oroIssuePriority(Schema $schema)
    {
        if ($schema->hasTable(self::ISSUE_PRIORITY_TABLE)) {
            $schema->dropTable(self::ISSUE_PRIORITY_TABLE);
        }
        $table = $schema->createTable(self::ISSUE_PRIORITY_TABLE);
        $table->addColumn('id', 'string', ['length' => 255]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->addColumn('order_num', 'integer');
        $table->setPrimaryKey(['id']);
    }

    /**
     * Generate table oro_issue_resolution
     *
     * @param Schema $schema
     */
    protected static function oroIssueResolution(Schema $schema)
    {
        if ($schema->hasTable(self::ISSUE_RESOLUTION_TABLE)) {
            $schema->dropTable(self::ISSUE_RESOLUTION_TABLE);
        }
        $table = $schema->createTable(self::ISSUE_RESOLUTION_TABLE);
        $table->addColumn('id', 'string', ['length' => 255]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->addColumn('order_num', 'integer');
        $table->setPrimaryKey(['id']);
    }

    protected static function addCollaborators(Schema $schema)
    {
        $table = $schema->createTable(self::ISSUE_COLLABORATOR_TABLE);
        $table->addColumn('user_id', 'integer');
        $table->addColumn('issue_id', 'integer');
        $table->setPrimaryKey(['issue_id', 'user_id']);
        $table->addIndex(['user_id']);
        $table->addIndex(['issue_id']);
    }

    /**
     * @param Schema $schema
     * @param CommentExtension $commentExtension
     */
    protected static function addComment(Schema $schema, CommentExtension $commentExtension)
    {
        $commentExtension->addCommentAssociation($schema, self::ISSUE_TABLE);
    }

    /**
     * @param Schema $schema
     * @param NoteExtension $noteExtension
     */
    protected static function addNoteAssociation(Schema $schema, NoteExtension $noteExtension)
    {
        $noteExtension->addNoteAssociation($schema, self::ISSUE_TABLE);
    }

    protected static function addActivityAssociation(Schema $schema, ActivityExtension $activityExtension)
    {
        $activityExtension->addActivityAssociation($schema, 'oro_email', 'oro_issue');
    }

    /**
     * Generate foreign keys for table oro_issue
     *
     * @param Schema $schema
     */
    protected static function addForeignKeysForIssue(Schema $schema)
    {
        $table = $schema->getTable('oro_issue');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['reporter_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['assignee_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_issue'),
            ['parent_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_issue_priority'),
            ['issue_priority_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_issue_resolution'),
            ['issue_resolution_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_item'),
            ['workflow_item_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_step'),
            ['workflow_step_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    protected static function addAddForeignKeysForCollaborator(Schema $schema)
    {
        $table = $schema->getTable('oro_issue_collaborator');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_issue'),
            ['issue_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
