<?php

namespace Oro\Bundle\IssueBundle\ImportExport\Converter;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;

class IssueDataConverter extends AbstractTableDataConverter
{
    /**
     * {@inheritDoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'id' => 'id',
            'code' => 'code',
            'summary'    => 'summary',
            'reporter' => 'reporter:username',
            'assignee' => 'assignee:username',
            'description' => 'description',
            'issue_type' => 'issueType',
            'updated_at'  => 'updatedAt',
            'created_at' => 'createdAt',
            'priority' => 'issuePriority:id',
            'tags'     => 'tags:name',
            'resolution' => 'issueResolution:id'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        return array_values($this->getHeaderConversionRules());
    }
}
