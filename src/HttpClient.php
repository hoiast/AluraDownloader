<?php

namespace Hoiast\AluraDownloader;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils as PromiseUtils;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class HttpClient
{
    private string $username;
    private string $password;
    private string $accessToken;
    private string $cookies;
    private array $promises = [];
    private Client $client;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->login();
    }

    private function login(): void
    {
        echo "Logging in..." . PHP_EOL;

        // Set clean client
        $this->setClient();

        // Get fresh cookies and access token
        $response = $this->client->request('POST', '/mobile/token', [
            'form_params' => [
                'username' => $this->username,
                'password' => $this->password,
                'client_secret' => '3de44ac5f5bccbcfba14a77181fbdbb9',
                'client_id' => 'br.com.alura.mobi',
                'grant_type' => 'password'
            ]
        ]);
        $this->cookies = $response->getHeader('Set-Cookie')[1];
        $this->accessToken = json_decode($response->getBody()->getContents())->access_token;

        // Set client with cookies and access token
        $this->setClient([
            'Cookie' => $this->cookies,
            'Authorization' => 'Bearer ' . $this->accessToken,
        ]);
    }

    private function setClient(array $headers = []): void
    {
        $this->client = new Client([
            'base_uri' => 'https://cursos.alura.com.br',
            'headers' => array_merge(
                [
                    'User-Agent' => 'alura-mobi/android-79',
                    'Host' => 'cursos.alura.com.br',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                $headers
            ),
        ]);
    }

    public function downloadFile(string $fileURL, string $filePath, string $fileTitle): void
    {
        echo "Initializing download of $fileTitle" . PHP_EOL;

        // Initialize async download (Promise)
        $promise = $this->client->requestAsync('GET', $fileURL, [
            'sink' => $filePath,
        ])->then(
            function (ResponseInterface $response) use ($fileTitle) {
                echo "Download complete: $fileTitle" . PHP_EOL;
            },
            function (RequestException $exception) use ($fileTitle) {
                echo "Download failed: $fileTitle" . PHP_EOL;
                echo $exception->getMessage() . "\n";
            }
        );

        // Add promise to httpClient for further management.
        $this->addPromise($promise);
    }

    private function addPromise(PromiseInterface $promise): void
    {
        $this->promises[] = $promise;
    }

    public function awaitPromises(): void
    {
        PromiseUtils::settle($this->promises)->wait();
    }

    public function get(string $uri, array $options = [])
    {
        $response = $this->client->request('GET', $uri, $options);
        $json = $response->getBody()->getContents();
        return json_decode($json, true);
    }
}
