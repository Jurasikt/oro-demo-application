<?php

namespace Oro\Bundle\IssueBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

use Oro\Bundle\IssueBundle\Model\ExtendIssue;

/**
 * Issue
 *
 * @ORM\Entity
 * @ORM\Table(name="oro_issue")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Oro\Bundle\IssueBundle\Entity\Repository\IssueRepository")
 * @Config(
 *      defaultValues={
 *          "workflow"={
 *              "active_workflow"="oro_issue_flow",
 *              "show_step_in_grid"=false
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="reporter",
 *              "owner_column_name"="reporter_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "category"="account_management"
 *          },
 *          "tag"={
 *              "enabled"=true
 *          },
 *          "note"={
 *              "enabled"=true
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 */
class Issue extends ExtendIssue implements DatesAwareInterface
{
    const ISSUE_TYPE_BUG = 'bug';
    const ISSUE_TYPE_STORY = 'story';
    const ISSUE_TYPE_TASK = 'task';
    const ISSUE_TYPE_SUBTASK = 'subtask';


    use DatesAwareTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=0,
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="issue_type", type="string", length=255)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *               "order"=10
     *          }
     *      }
     * )
     */
    protected $issueType;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="reporter_id", referencedColumnName="id", nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *               "order"=20
     *          }
     *      }
     * )
     */
    protected $reporter;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="assignee_id", referencedColumnName="id")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *               "order"=30
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $assignee;

    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="string", length=255)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *               "order"=40
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $summary;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, unique=true, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *               "order"=15
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *               "order"=50
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $description;

    /**
     * @var IssuePriority
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\IssueBundle\Entity\IssuePriority")
     * @ORM\JoinColumn(name="issue_priority_id", referencedColumnName="id")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *               "order"=60
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $issuePriority;

    /**
     * @var IssueResolution
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\IssueBundle\Entity\IssueResolution")
     * @ORM\JoinColumn(name="issue_resolution_id", referencedColumnName="id")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *               "order"=70
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $issueResolution;

    /**
     * @var Issue
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\IssueBundle\Entity\Issue")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *               "excluded"=true
     *          }
     *      }
     * )
     */
    protected $parent;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinTable(name="oro_issue_collaborator",
     *     joinColumns={@ORM\JoinColumn(name="issue_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *               "excluded"=true
     *          }
     *      }
     * )
     */
    protected $collaborators;

    /**
     * @var WorkflowItem
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowItem")
     * @ORM\JoinColumn(name="workflow_item_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowItem;

    /**
     * @var WorkflowStep
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowStep")
     * @ORM\JoinColumn(name="workflow_step_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowStep;

    /**
     * @var OrganizationInterface
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *               "excluded"=true
     *          }
     *      }
     * )
     */
    protected $organization;

    /**
     * @var bool
     */
    protected $updateAtSet = false;

    public function __construct()
    {
        $this->collaborators = new ArrayCollection();
        parent::__construct();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIssueType()
    {
        return $this->issueType;
    }

    /**
     * @param string $issueType
     * @return Issue
     */
    public function setIssueType($issueType)
    {
        $this->issueType = $issueType;

        return $this;
    }

    /**
     * @param User $reporter
     * @return Issue
     */
    public function setReporter($reporter)
    {
        $this->reporter = $reporter;
        return $this;
    }

    /**
     * @param User $assignee
     * @return Issue
     */
    public function setAssignee($assignee)
    {
        $this->assignee = $assignee;
        return $this;
    }

    /**
     * @return User
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * @return User
     */
    public function getAssignee()
    {
        return $this->assignee;
    }

    /**
     * Set summary
     *
     * @param string $summary
     * @return Issue
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Issue
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Issue
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return IssuePriority
     */
    public function getIssuePriority()
    {
        return $this->issuePriority;
    }

    /**
     * @param IssuePriority $issuePriority
     * @return Issue
     */
    public function setIssuePriority(IssuePriority $issuePriority)
    {
        $this->issuePriority = $issuePriority;

        return $this;
    }

    /**
     * @return IssueResolution
     */
    public function getIssueResolution()
    {
        return $this->issueResolution;
    }

    /**
     * @param IssueResolution $issueResolution
     * @return Issue
     */
    public function setIssueResolution(IssueResolution $issueResolution)
    {
        $this->issueResolution = $issueResolution;

        return $this;
    }

    /**
     * @return Issue
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Issue $parent
     * @return Issue
     */
    public function setParent(Issue $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @param WorkflowItem $workflowItem
     * @return Issue
     */
    public function setWorkflowItem($workflowItem)
    {
        $this->workflowItem = $workflowItem;

        return $this;
    }

    /**
     * @return WorkflowItem
     */
    public function getWorkflowItem()
    {
        return $this->workflowItem;
    }

    /**
     * @param WorkflowItem $workflowStep
     * @return Issue
     */
    public function setWorkflowStep($workflowStep)
    {
        $this->workflowStep = $workflowStep;

        return $this;
    }

    /**
     * Set organization
     *
     * @param OrganizationInterface $organization
     * @return Issue
     */
    public function setOrganization(OrganizationInterface $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return OrganizationInterface
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param User $user
     */
    public function addCollaborator(User $user)
    {
        if (! $this->containsCollaborator($user)) {
            $this->collaborators->add($user);
        }
    }

    /**
     * @param User $user
     */
    public function removeCollaborator(User $user)
    {
        if ($this->containsCollaborator($user)) {
            $this->collaborators->removeElement($user);
        }
    }

    /**
     * @param User $user
     * @return bool
     */
    public function containsCollaborator(User $user)
    {
        return $this->collaborators->contains($user);
    }

    /**
     * @return Collection
     */
    public function getCollaborators()
    {
        return $this->collaborators;
    }

    /**
     * @param Collection $collaborators
     */
    public function setCollaborators(Collection $collaborators)
    {
        $this->collaborators = $collaborators;
    }

    /**
     * Get workflow status
     * @return  string|null
     */
    public function getStatus()
    {
        return ($this->getWorkflowStep() instanceof WorkflowStep) ? $this->getWorkflowStep()->getLabel() : null;
    }

    /**
     * @return WorkflowStep
     */
    public function getWorkflowStep()
    {
        return $this->workflowStep;
    }

    /**
     * Generate and set unique id for issue code
     */
    public function setUniqueCode()
    {
        if (null === $this->id) {
            $this->code = uniqid('ATT-');
        } else {
            $this->code = sprintf('ATT-%s', $this->id);
        }
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->createdAt = $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
