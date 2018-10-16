<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 07.10.2014
 * Time: 16:35
 */

namespace v4\ispconfig;
require_once __DIR__ .'/../repositories/ServerRepository.php';
require_once __DIR__ .'/../models/ISPCSoapClient.php';

class ServerRepositoryTest extends \PHPUnit_Framework_TestCase
{
    protected $repo;
    public function setUp()
    {
        $client = new ISPCSoapClient();
        //$client->login('infoathand', 'zk_RiLcD', 'https://vs2.visual4.com:8080/remote/');
        $client->login('1crm', '123546789', 'http://ispconfig.local.dev/interface/web/remote/');
        $this->repo = new ServerRepository($client);
    }

    public function testServerReturn(){
        $server = $this->repo->getById(2);
        $this->assertEquals('vs5.visual4.com', $server->getHostname());
        $this->assertEquals(2, $server->getId());
        $this->assertEquals('94.186.153.202', $server->getIpAddress());
    }

    public function testGetAll(){
        $serverarray = $this->repo->getAll();
        var_dump($serverarray);

        $this->assertTrue(count($serverarray) > 5);
    }
}
 