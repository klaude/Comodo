<?php
namespace Checkdomain\Comodo\Tests;

class UtilTest extends AbstractTest
{
    /**
     * test for applying SSL
     */
    public function testAutoApplySSL()
    {
        // simulated response Text
        $responseText = "errorCode=1&";
        $responseText .= "totalCost=12.98&";
        $responseText .= "orderNumber=123456789&";
        $responseText .= "certificateID=abc123456&";
        $responseText .= "expectedDeliveryTime=123456&";

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $params = array(
            "test"                => "Y",
            "product"             => 287,
            "serverSoftware"      => 2,
            "csr"                 => ("-----BEGIN CERTIFICATE REQUEST-----base64-----END CERTIFICATE REQUEST-----"),
            "isCustomerValidated" => "Y",
            "showCertificateID"   => "Y",
            "days"                => 365,
        );

        $object = $util->autoApplySSL($params);

        $this->assertInstanceOf('\Checkdomain\Comodo\Model\Result\AutoApplyResult', $object);

        $this->assertEquals("12.98", $object->getTotalCost());
        $this->assertEquals("123456", $object->getExpectedDeliveryTime());
        $this->assertEquals("abc123456", $object->getCertificateID());
        $this->assertEquals("123456789", $object->getOrderNumber());
        $this->assertEquals(false, $object->getPaid());
    }

    /**
     * test for getting dvc mail addresses
     */
    public function testGetDCVEMailAddressList()
    {
        // simulated response Text
        $responseText = "0\n";
        $responseText .= "domain_name	www.test-domain.org\n";
        $responseText .= "whois_email	 support@test-domain.org\n";
        $responseText .= "level2_email	 admin@test-domain.org\n";
        $responseText .= "level2_email	 postmaster@test-domain.org\n";
        $responseText .= "level3_email	 admin@www.test-domain.org\n";
        $responseText .= "level3_email	 postmaster@www.test-domain.org\n";

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $params = array(
            "domainName" => "www.test-domain.org"
        );

        $object = $util->getDCVEMailAddressList($params);

        $this->assertInstanceOf('\Checkdomain\Comodo\Model\Result\GetDCVEMailAddressListResult', $object);

        $this->assertEquals(array("support@test-domain.org"), $object->getWhoisEmail());
        $this->assertEquals("www.test-domain.org", $object->getDomainName());

        $this->assertEquals(array('admin@test-domain.org', 'postmaster@test-domain.org'), $object->getLevel2Emails());
        $this->assertEquals(array('admin@www.test-domain.org', 'postmaster@www.test-domain.org'),  $object->getLevel3Emails());
    }

    /**
     * test for resending dcv mail
     */
    public function testResendDCVEMail()
    {
        // simulated response Text
        $responseText = "errorCode=0";

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $params = array(
            "orderNumber"     => "1234567",
            "dcvEmailAddress" => "webmaster@tobias-nitsche.de",
        );

        $return = $util->resendDCVEMail($params);

        $this->assertEquals(true, $return);
    }

    /**
     * test for entering dcv code
     */
    public function testEnterDCVCode()
    {
        // simulated response Text
        $responseText = "<html><body><p>You have entered the correct Domain Control Validation code. ";
        $responseText .= "Your certificate will now be issued and emailed to you shortly. ";
        $responseText .= "Please close this window now.";
        $responseText .= "</p></body></html>";

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $params = array(
            "orderNumber" => "1234567",
            "dcvCode"     => "testtesttest",
        );

        $return = $util->enterDcvCode($params);

        $this->assertEquals(true, $return);
    }

    /**
     * test for revoke ssl
     */
    public function testAutoRevokeSSL()
    {
        $responseText = "errorCode=0";

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $params = array(
            "orderNumber" => "1234567"
        );

        $return = $util->autoRevokeSSL($params);

        $this->assertEquals(true, $return);
    }

    /**
     * test for auto replacing ssl
     */
    public function testAutoReplaceSSL()
    {
        $responseText = 'errorCode=0&expectedDeliveryTime=0&certificateID=abc123456&';

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $params = array(
            'orderNumber' => '1234567'
        );

        $object = $util->autoReplaceSSL($params);

        $this->assertInstanceOf('\Checkdomain\Comodo\Model\Result\AutoReplaceResult', $object);

        $this->assertEquals('0', $object->getExpectedDeliveryTime());
        $this->assertEquals('abc123456', $object->getCertificateID());
    }

    /**
     * test for auto updating dcv method
     */
    public function testAutoUpdateDcv()
    {
        $responseText = 'errorCode=0&expectedDeliveryTime=0&certificateID=abc123456&';

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $params = array(
            'orderNumber'        => '1234567',
            'newDCVEmailAddress' => 'postmaster@test.de',
            'newMethod'          => 'EMAIL',
        );

        $return = $util->autoUpdateDCV($params);

        $this->assertEquals(true, $return);
    }

    /**
     * test for providing ev details
     */
    public function testProvideEvDetails()
    {
        $responseText = 'errorCode=0';

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $params = array(
            'orderNumber'     => '1234567',
            'certReqForename' => 'John',
            'certReqSurname'  => 'Test',

        );

        $return = $util->autoUpdateDCV($params);

        $this->assertEquals(true, $return);
    }

    /**
     * test for getting current dcv method
     */
    public function testGetMdcDomainDetails()
    {

        $responseText = 'errorCode=0&1_domainName=test.com&1_dcvMethod=EMAIL&1_dcvStatus=Validated';

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $params = array(
            'orderNumber' => '1234567',
        );

        $object = $util->getMDCDomainDetails($params);

        $this->assertInstanceOf('\Checkdomain\Comodo\Model\Result\GetMDCDomainDetailsResult', $object);

        $this->assertEquals('test.com', $object->getDomainName());
        $this->assertEquals('EMAIL', $object->getDcvMethod());
        $this->assertEquals('Validated', $object->getDcvStatus());
    }

    /**
     * test, if request-string is correctly formatted
     */
    public function testCheckRequestString()
    {
        $params = array(
            'orderNumber'     => '1234567',
            'dcvMethod'       => 'EMAIL',
        );

        $responseText = 'errorCode=0&certificateID=abc1231556&expectedDeliveryTime=0&orderNumber=12345678&totalCost=12.45';

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $object = $util->autoApplySSL($params);

        $this->assertEquals('orderNumber=1234567&dcvMethod=EMAIL&responseFormat=1&showCertificateID=Y', $object->getRequestQuery());
    }

    /**
     * @expectedException \Checkdomain\Comodo\Model\Exception\RequestException
     */
    public function testRequestException()
    {
        $responseText = 'errorCode=-1&errorMessage=Invalid Request';

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $util->autoApplySSL(array());
    }

    /**
     * @expectedException \Checkdomain\Comodo\Model\Exception\ArgumentException
     */
    public function testArgumentException()
    {
        $responseText = 'errorCode=-2&errorItem=field&errorMessage=Invalid Request';

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $util->autoApplySSL(array());
    }

    /**
     * @expectedException \Checkdomain\Comodo\Model\Exception\AccountException
     */
    public function testAccountException()
    {
        $responseText = 'errorCode=-15&errorMessage=Invalid Request';

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $util->autoApplySSL(array());
    }

    /**
     * @expectedException \Checkdomain\Comodo\Model\Exception\CsrException
     */
    public function testCsrException()
    {
        $responseText = 'errorCode=-5&errorMessage=Invalid Request';

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $util->autoApplySSL(array());
    }

    /**
     * @expectedException \Checkdomain\Comodo\Model\Exception\UnknownApiException
     */
    public function testUnknownApiException()
    {
        $responseText = 'errorCode=-14&errorMessage=Invalid Request';

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $util->autoApplySSL(array());
    }

    /**
     * @expectedException \Checkdomain\Comodo\Model\Exception\UnknownException
     */
    public function testUnknownException()
    {
        $responseText = 'Internal Server Error';

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $util->autoApplySSL(array());
    }

    /**
     * test, for getting status of certificate
     */
    public function testCollectSslStatus()
    {
        $responseText = 'errorCode=1&orderNumber=12345678&certificateStatus=Issued';

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $object = $util->collectSsl(array('showExtStatus' => 'Y'));

        $this->assertEquals('Issued', $object->getCertificateStatus());
    }


    /**
     * test, for getting status of certificate
     */
    public function testUpdateUserEvClickThrough()
    {
        $responseText = 'errorCode=0&status=1';

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $object = $util->updateUserEvClickThrough(array());

        $this->assertEquals(1, $object->getStatus());
    }

    /**
     * test, for getting period of certificate
     */
    public function testCollectSslPeriod()
    {
        $responseText = 'errorCode=1&orderNumber=12345678&notBefore=1388576001&notAfter=1420112001&csrStatus=4&certificateStatus=3&validationStatus=1&certificate=234&caCertificate=123&ovCallBackStatus=2';

        $util = $this->createUtil($this->createGuzzleClient($responseText));

        $object = $util->collectSsl(array());

        $caCertificate = ['-----BEGIN CERTIFICATE-----' .PHP_EOL . '123' .  PHP_EOL . '-----END CERTIFICATE-----'];
        $certificate = '-----BEGIN CERTIFICATE-----' . PHP_EOL .'234' .  PHP_EOL . '-----END CERTIFICATE-----';

        $this->assertEquals($caCertificate, $object->getCaCertificate() );
        $this->assertEquals($certificate, $object->getCertificate());
        $this->assertEquals('1', $object->getValidationStatus() );
        $this->assertEquals('2', $object->getOvCallBackStatus() );
        $this->assertEquals('3', $object->getCertificateStatus() );
        $this->assertEquals('4', $object->getCsrStatus() );
        $this->assertEquals('12345678', $object->getOrderNumber() );
        $this->assertEquals('01.01.2014', $object->getNotBefore()->format('d.m.Y') );
        $this->assertEquals('01.01.2015', $object->getNotAfter()->format('d.m.Y') );
    }


}
