<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
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
        // Create the cache key
        $cacheKey = 'weather_data';
        $cacheItem = $this->cache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            // Fetch and cache the data from the Open-Meteo API endpoint
            $weatherData = $this->cache->get($cacheItem, function (ItemInterface $item) {
                // the result will disapear after 300 sec (5min)
                $item->expiresAfter(300);

                $url = 'https://api.open-meteo.com/v1/forecast?latitude=52.52&longitude=13.41&current=temperature_2m&hourly=temperature_2m&forecast_days=1';

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
        } else {
            return $this->json(['data' => $cacheItem->get(), 'Hits' => 'Data fetched from Cache', 'status' => 200]);
        }
    }
}
