<?php

namespace Oro\Bundle\IssueBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ImportExportTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
    }

    public function testExport()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_importexport_export_instant',
                [
                    'processorAlias' => 'oro_issue_export',
                    '_format'        => 'json'
                ]
            )
        );

        $data = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertTrue($data['success']);
        $this->assertEquals($data['errorsCount'], 0);
    }

    public function testLoadExportTemplate()
    {
        $this->client->followRedirects(true);

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_importexport_export_template',
                [
                    'processorAlias' => 'oro_issue_export',
                    '_format'        => 'json'
                ]
            )
        );

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($response, 200);
        $this->assertResponseContentTypeEquals($response, 'text/csv');
    }
}
