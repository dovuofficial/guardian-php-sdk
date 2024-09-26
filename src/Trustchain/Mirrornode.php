<?php

namespace Dovu\GuardianPhpSdk\Trustchain;

use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp;

/**
 * Fluent class for mirrornode requests
 */
enum StateFilter: string
{
    case GT = 'gt';
    case LT = 'lt';
}

enum PublicMirrornodeUrl: string
{
    case TESTNET = 'https://testnet.mirrornode.hedera.com';
    case PRODUCTION = 'https://mainnet-public.mirrornode.hedera.com';
}

class Mirrornode
{
    private const MIRRORNODE_WAIT_MS = 500;
    private const MIRRORNODE_TRIES = 5;

    private string $mirrornode_request_url;

    private string $mirrornode;

    private string $api_key = '';

    public function __construct(public string $path)
    {
        $this->mirrornode = PublicMirrornodeUrl::PRODUCTION->value;
        $this->mirrornode_request_url = $this->mirrornode . '/api/v1' . $path;
    }

    public function forCustomUrl(string $url)
    {
        $this->setMirrornodeRequestUrl($url);

        return $this;
    }
    public function forTestnet()
    {
        $this->setMirrornodeRequestUrl(PublicMirrornodeUrl::TESTNET->value);

        return $this;
    }

    public function forProduction()
    {
        $this->setMirrornodeRequestUrl(PublicMirrornodeUrl::PRODUCTION->value);

        return $this;
    }

    /**
     * This next sequence of methods are static factories, This enables us to set a particular context for the mirrornode query we are making.
     */

    public static function credits($token_id): self
    {
        $path = "/tokens/{$token_id}/nfts?limit=100";

        return new self($path);
    }

    public static function message($ts): self
    {
        $path = "/topics/messages/$ts";

        return new self("/topics/messages/$ts");
    }

    public static function account($account): self
    {
        $path = "/accounts/{$account}?limit=100";

        return new self($path);
    }

    private function getResponse(): ?object
    {
        return $this->retryableRequest($this->mirrornode_request_url);
    }

    /**
     * This allows us to retry against mirrornode,so it doesn't die on first failure, it expects a query
     * so that internal methods can make pagination requests
     *
     * @throws Exception
     */
    private function retryableRequest($query, $tries = self::MIRRORNODE_TRIES, $attempts = 1)
    {
        $client = new GuzzleHttp\Client();

        try {
            $response = $client->request('GET', $query, [
                'headers' => [
                    'x-api-key' => $this->api_key
                ]
            ]);

            return (object) json_decode($response->getBody());

        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();

                if ($statusCode === 404) {
                    return (object) [
                        'error' => [
                            'message' => "Expected resource was not found, for query ['$query']",
                            'query' => $query
                        ]
                    ];
                }
            }

            if ($attempts > $tries) {
                return (object) [
                    'error' => [
                        "Hedera Mirrornode Overloaded after {$tries} attempts, unable to process query"
                    ]
                ];
            }

            error_log("mirrornode overloaded, retrying in " . self::MIRRORNODE_WAIT_MS . " ms, current attempt {$attempts} of {$tries} tries");

            usleep(self::MIRRORNODE_WAIT_MS);

            return $this->retryableRequest($query, $tries, ++$attempts);
        }
    }

    public function paginatedFetch(string $property, array $collector = [])
    {
        $response = $this->getResponse();

        $updated = [...$collector, ...$response->{$property}];

        $next = $response->links->next;

        if (!$next) {
            return $updated;
        }

        return $this->setPath($next)->paginatedFetch($property, $updated);
    }

    private function setPath($path): self
    {
        $this->mirrornode_request_url = $this->mirrornode . $path;

        return $this;
    }

    public function transactions(): array
    {
        return $this->paginatedFetch('transactions');
    }

    public function forTransfers(): self
    {
        $this->mirrornode_request_url .= '&transactionType=cryptotransfer';

        return $this;
    }

    // Return all data

    public function atTimestamp(string $epoch, StateFilter $filter = StateFilter::LT): self
    {
        $this->mirrornode_request_url .= "&timestamp={$filter->value}:{$epoch}";

        return $this;
    }

    /**
     * Reduces the wasteful fetch of data to be returned when processing credit provenance.
     *
     * @param int $serial
     * @param StateFilter $filter
     * @return $this
     */
    public function fromSerial(int $serial, StateFilter $filter = StateFilter::GT): self
    {
        $this->mirrornode_request_url .= "&serialnumber={$filter->value}:{$serial}";

        return $this;
    }

    public function fetch(): ?object
    {
        return $this->getResponse();
    }

    /**
     * @throws Exception
     */
    public function exists(): bool
    {
        $response = $this->retryableRequest($this->mirrornode_request_url, 1);

        return !!$response->account;
    }

    public function setMirrornodeRequestUrl(string $mirror_url): void
    {
        $this->mirrornode = $mirror_url;
        $this->mirrornode_request_url = rtrim($this->mirrornode, '/\\') . '/api/v1' . $this->path;
    }

    public function setApiKey(string $api_key): Mirrornode
    {
        $this->api_key = $api_key;

        return $this;
    }
}

