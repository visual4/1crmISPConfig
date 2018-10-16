<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 07.10.2014
 * Time: 18:03
 */

namespace v4\ispconfig;

require_once __DIR__ .'/../repositories/ClientRepository.php';
require_once __DIR__ .'/../models/ISPCSoapClient.php';

class ClientRepositoryTest extends \PHPUnit_Framework_TestCase {

    /** @var  ClientRepository */
    protected $repo;
    public function setUp()
    {
        $client = new ISPCSoapClient();
        $client->login('1crm', 'asdftzu', 'http://ispconfig.local.dev/interface/web/remote/');
        $this->repo = new ClientRepository($client);
    }

    public function  testGetAll(){
        return;
        $clientarray = $this->repo->getAll();
        var_dump($clientarray);
        $this->assertTrue(count($clientarray) > 20);
    }

    public function testSaveExisting(){
        $client = new Client();
        $client->setEmail('info@visual4.de')->setCompanyName('TestLevel 9')->setContactName('Björn Rafreider')->setCustomerNo('XXXYY35')->setId(4);
        $result = $this->repo->saveToISPC($client);
        $this->assertEquals($result, array());

    }

    public function testAddNew(){
        $client = new Client();
        $client->setEmail('info@visual4.de')->setCompanyName('TestKunde GmbH')->setContactName('Björn Rafreider')->setCustomerNo('XXX35');
        $result = $this->repo->saveToISPC($client);
        $this->assertEquals($result, array());

    }
}
 