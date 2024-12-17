<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WeatherControllerTest extends WebTestCase
{
    public function testWeatherEndpoint()
    {
        $client = static::createClient();
        $client->request('GET', '/weather?latitude=52.52&longitude=13.41');
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}