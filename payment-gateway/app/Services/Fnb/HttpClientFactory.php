<?php

namespace App\Services\Fnb;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

class HttpClientFactory
{
    public function __construct(
        private readonly string $baseUri,
        private readonly string $oauthUri,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly ?string $certPath = null,
        private readonly ?string $certKeyPath = null,
        private readonly int $timeout = 10,
        private readonly int $connectTimeout = 5,
        private readonly int $retryAttempts = 3,
        private readonly int $retryDelayMs = 200,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function make(): Client
    {
        $stack = HandlerStack::create();

        $stack->push(Middleware::retry(function ($retries, $request, $response, $exception) {
            if ($retries >= $this->retryAttempts) {
                return false;
            }

            if ($exception !== null) {
                $this->logger()?->warning('FNB request failed, retrying', [
                    'exception' => $exception->getMessage(),
                    'request_id' => $request->getHeaderLine('X-Request-Id'),
                    'retries' => $retries,
                ]);
                return true;
            }

            if ($response && in_array($response->getStatusCode(), [429, 500, 502, 503, 504], true)) {
                $this->logger()?->warning('FNB request returned retryable status', [
                    'status' => $response->getStatusCode(),
                    'retries' => $retries,
                ]);
                return true;
            }

            return false;
        }, function () {
            return $this->retryDelayMs / 1000;
        }));

        $config = [
            'base_uri' => $this->baseUri,
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectTimeout,
            'handler' => $stack,
        ];

        if ($this->certPath) {
            $config['cert'] = $this->certKeyPath
                ? [$this->certPath, $this->certKeyPath]
                : $this->certPath;
        }

        return new Client($config);
    }

    public function makeWithToken(): Client
    {
        $cacheKey = 'fnb.oauth.token';

        $token = Cache::get($cacheKey);

        if (!$token) {
            $client = new Client([
                'base_uri' => $this->oauthUri,
                'timeout' => $this->timeout,
            ]);

            $response = $client->post('', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            $expiresIn = max((int) ($payload['expires_in'] ?? 3600) - 60, 60);

            $token = $payload['access_token'];

            Cache::put($cacheKey, $token, now()->addSeconds($expiresIn));
        }

        $client = $this->make();

        $client->getConfig('headers')['Authorization'] = 'Bearer '.$token;

        return new Client(array_merge($client->getConfig(), [
            'headers' => array_merge($client->getConfig('headers') ?? [], [
                'Authorization' => 'Bearer '.$token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]),
        ]));
    }

    private function logger(): ?LoggerInterface
    {
        return $this->logger ?? Log::channel('stack');
    }
}
