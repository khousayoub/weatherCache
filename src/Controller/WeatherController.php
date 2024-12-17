<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;

    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    #[Route('/weather', name: 'weather', methods: ['GET'])]
    public function getWeather(Request $request): JsonResponse
    {
        // Get latitude and longitude from query parameters Default to Cancer Research London
        $latitude = $request->query->get('latitude', 51.5196);
        $longitude = $request->query->get('latitude', -0.1541);

        // Cache key with dynamic coordinates
        $cacheKey = 'weather_data_'.$latitude.'_'.$longitude;

        // Fetch and cache the data from the Open-Meteo API endpoint
        $weatherData = $this->cache->get($cacheKey, function ($latitude, $longitude) {
            $url = "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&current_weather=true&hourly=temperature_2m&forecast_days=1";
            
            // Error handling
            try {
                $response = $this->httpClient->request('GET', $url);
            } catch (\Exception $e) {
                return $this->json([
                    'error' => 'Failed to fetch data from Open-Meteo API.',
                    'message' => $e->getMessage(),
                ], 500);
            }

            return $response->toArray();
        });

        return $this->json(['data' => $weatherData, 'status' => 200]);
    }
}
