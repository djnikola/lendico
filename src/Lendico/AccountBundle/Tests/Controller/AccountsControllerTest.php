<?php

namespace Lendico\AccountBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Lendico\AccountBundle\Entity\Account;

class AccountsControllerTest extends WebTestCase
{
    
    /**
     * Testing get method of API account.
     */
    public function testGetAccounts() 
    {

        $client = static::createClient();

        $client->request('GET', '/api/v1/accounts/1.json');
        $response = $client->getResponse();

        $this->assertEquals(
            200, $response->getStatusCode(),
            $response->getContent()
        );

        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            $response->headers
        );
        
        
    }
    
    /**
     * Test of creating a account
     */
    public function testCreateAccount()
    {
        $account_name = "test-". date('Y-m-d-h:m:s');
        
        $client = static::createClient();
        // Submit a raw JSON string in the request body
        $client->request(
            'POST',
            '/api/v1/accounts.json',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"name":"'. $account_name .'","status":"active"}'
        );

        $response = $client->getResponse();
        
        $this->assertEquals(
            201, $response->getStatusCode(),
            $response->getContent()
        );
        
    }
    
    /**
     * Testing deactivating an account.
     */
    public function testInactiveAccount()
    {
        //based on load Data Fixtures.
        $account_id = 3;
        
        $client = static::createClient();
        // Call PUT method
        $client->request(
            'PUT',
            "/api/v1/accounts/$account_id.json",
            array(),
            array(),
            array()
        );

        $response = $client->getResponse();
        
        $this->assertEquals(
            204, $response->getStatusCode(),
            "Account is not set inactive!"
        );
        
        
        $client->request('GET', "/api/v1/accounts/$account_id.json");
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(($content["status"] === "inactive"), "Account retreived as active!");
        
    }
    
    /**
     * testing adding deposit to account.
     */
    public function testDepositAmount()
    {
         //based on load Data Fixtures.
        $account_id = 1;
        $amount = 1;
        $currency = "USD";
        
        $client = static::createClient();
        $client->request("GET", "/api/v1/accounts/$account_id.json");
        $before_saldo = $this->getSaldoFromContent($client->getResponse()->getContent(), $currency);
        
        // Submit a raw JSON string in the request body
        $client->request(
            "PUT",
            "/api/v1/accounts/$account_id/amounts/$amount/currencies/$currency/opers/+.json",
            array(),
            array(),
            array()
        );
        
        $this->assertNotEquals(
            404, $client->getResponse()->getStatusCode(),
            "Account not found!"
        );
        
        
        $client->request("GET", "/api/v1/accounts/$account_id.json");
        $after_saldo = $this->getSaldoFromContent($client->getResponse()->getContent(), $currency);
        $this->assertTrue( ($before_saldo + $amount === $after_saldo), "Deposit failed!");
    }
    
    /**
     * testing Withdraw deposit to account.
     */
    public function testWithdrawAmount()
    {
         //based on load Data Fixtures.
        $account_id = 1;
        $amount = 1;
        $currency = "USD";
        
        $client = static::createClient();
        $client->request("GET", "/api/v1/accounts/$account_id.json");
        $before_saldo = $this->getSaldoFromContent($client->getResponse()->getContent(), $currency);
        
        // Submit a raw JSON string in the request body
        $client->request(
            "PUT",
            "/api/v1/accounts/$account_id/amounts/$amount/currencies/$currency/opers/-.json",
            array(),
            array(),
            array()
        );
        
        $this->assertNotEquals(
            404, $client->getResponse()->getStatusCode(),
            "Account not found!"
        );
        
        
        $client->request("GET", "/api/v1/accounts/$account_id.json");
        $after_saldo = $this->getSaldoFromContent($client->getResponse()->getContent(), $currency);
        $this->assertTrue( ($before_saldo - $amount === $after_saldo), "Deposit failed!");
    }
    
    /**
     * testing transfer deposit to account.
     */
    public function testTransferAmount()
    {
        //based on load Data Fixtures.
        $from_account_id = 1;
        $amount = 1;
        $currency = "USD";
        $to_account_id = 2;
        
        $client = static::createClient();
        $client->request("GET", "/api/v1/accounts/$from_account_id.json");
        $from_account_before_saldo = $this->getSaldoFromContent($client->getResponse()->getContent(), $currency);
        
        $client->request("GET", "/api/v1/accounts/$to_account_id.json");
        $to_account_before_saldo = $this->getSaldoFromContent($client->getResponse()->getContent(), $currency);
        
        // Submit a raw JSON string in the request body
        $client->request(
            "PUT",
            "/api/v1/accounts/$from_account_id/amounts/$amount/currencies/$currency/tos/$to_account_id.json",
            array(),
            array(),
            array()
        );
        
        $this->assertNotEquals(
            404, $client->getResponse()->getStatusCode(),
            "Account not found!"
        );
        
        
        $client->request("GET", "/api/v1/accounts/$from_account_id.json");
        $from_account_after_saldo = $this->getSaldoFromContent($client->getResponse()->getContent(), $currency);
        $this->assertTrue( (($from_account_before_saldo - $amount) === $from_account_after_saldo), "Widrawing amount transfer failed!");
        
        //checking destination acount.
        $client->request("GET", "/api/v1/accounts/$to_account_id.json");
        $to_account_after_saldo = $this->getSaldoFromContent($client->getResponse()->getContent(), $currency);
        
        $this->assertTrue( (($to_account_before_saldo + $amount) === $to_account_after_saldo), "Deposit amount transfer failed!");
        
    }
    
    /**
     * Returns saldo from content for provided currency of account.
     * 
     * @param string $content Response content
     * @param string $currency Currency of saldo
     * @return float
     */
    private function getSaldoFromContent($content, $currency)
    {
        $data = json_decode($content, true);
        if ( empty($data) || empty($data["amounts"]) || empty($data["amounts"][$currency]) ) {
            return 0;
        }
        return  $data["amounts"][$currency]["amount"];
    }
}
        