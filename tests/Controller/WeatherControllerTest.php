<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WeatherControllerTest extends WebTestCase
{
    public function testWeatherEndpoint()
    {
        $client = static::createClient();
        $client->request('GET', '/weather');
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}