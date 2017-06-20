<?php

namespace hyperia\security\tests;

use Yii;
use yii\base\Application;
use hyperia\security\Headers;

/**
 * Headers test
 */
class HeaderTest extends TestCase
{
    private $headers;

    /**
     * Set Up
     */
    protected function setUp()
    {
        parent::setUp();
        
        // run web application
        $this->mockApplication(require(__DIR__ . '/config/config.php'), 'yii\web\Application');
        
        // trigger event
        Yii::$app->trigger(Application::EVENT_BEFORE_REQUEST);
        
        // init extension
        $this->headers = new Headers();
    }

    /**
     * Data provider - default headers
     */
    public function defaultHeaders()
    {
        return [
            ['x-powered-by', 'Hyperia'],
            ['x-frame-options', 'DENY'],
            ['content-security-policy', 'default-src'],
            ['strict-transport-security', 'max-age=10'],
            ['x-content-type-options', 'nosniff'],
            ['x-xss-protection', '1; mode=block;']
        ];
    }
    
    /**
     * @dataProvider defaultHeaders
     */
    public function testHeaders($a, $b)
    {
        $defaultHeaders = Yii::$app->response->getHeaders();
        
        $this->assertNotEmpty($defaultHeaders);
        $this->assertCount(6, $defaultHeaders);
        $this->assertArrayHasKey($a, $defaultHeaders);
        $this->assertContains($b, $defaultHeaders[$a]);
    }
    
    /**
     * Test report uri header
     */
    public function testReportUri()
    {
        $this->headers->reportUri = 'https://companyname.report-uri.io';
        $reportUri = $this->invokeMethod($this->headers, 'getCspReportUri');

        $arrayKey = 'report-uri';
        $this->assertNotEmpty($reportUri);
        $this->assertArrayHasKey($arrayKey, $reportUri);
        $this->assertNotEmpty($reportUri[$arrayKey]);
        $this->assertContains($this->headers->reportUri, $reportUri[$arrayKey]);
    }

    /**
     * Test CSP headers
     */
    public function testDefaultCSP()
    {
        $csp = $this->invokeMethod($this->headers, 'getContentSecurityPolicyDirectives');

        $this->assertNotEmpty($csp);
        $this->assertContains('default-src', $csp);
        $this->assertContains('script-src', $csp);
        $this->assertContains('report-uri', $csp);
        $this->assertContains('block-all-mixed-content', $csp);
        $this->assertContains('upgrade-insecure-requests', $csp);
    }

    /**
     * Text disable mixed content 
     */
    public function testCSPWithDisableMixedContent()
    {
        $this->headers->blockAllMixedContent = false;
        $csp = $this->invokeMethod($this->headers, 'getContentSecurityPolicyDirectives');

        $this->assertNotEmpty($csp);
        $this->assertNotContains('block-all-mixed-content', $csp);
    }

    /**
     * Test disable insecure request
     */
    public function testCSPWithDisableInsecureRequest()
    {
        $this->headers->upgradeInsecureRequests = false;
        $csp = $this->invokeMethod($this->headers, 'getContentSecurityPolicyDirectives');

        $this->assertNotEmpty($csp);
        $this->assertNotContains('upgrade-insecure-requests', $csp);
    }
    
}