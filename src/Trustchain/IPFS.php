<?php

namespace Dovu\GuardianPhpSdk\Trustchain;

use Exception;
use GuzzleHttp;

enum IPFSGateway: string
{
    case DEFAULT = "https://ipfs.io/ipfs/" . IPFS::CID_PLACEHOLDER;
    case DWEB = "https://" . IPFS::CID_PLACEHOLDER . ".ipfs.dweb.link";
    case W3S = "https://" . IPFS::CID_PLACEHOLDER . ".ipfs.w3s.link";
}

class IPFS
{
    public const CID_PLACEHOLDER = "__CID__";
    public const REQUEST_TIMEOUT = 1;

    private string $ipfs;

    private int $request_timeout = self::REQUEST_TIMEOUT;

    public function __construct(public string $cid)
    {
        $this->ipfs = IPFSGateway::DEFAULT->value;
    }

    public function withIpfsGatewayFormat(string $url): self
    {
        if (str_contains($url, IPFS::CID_PLACEHOLDER)) {
            $this->ipfs = $url;

            return $this;
        }

        $placeholder = IPFS::CID_PLACEHOLDER;

        throw new Exception("The IPFS url [$url] must contain the CID placeholder format [$placeholder]");
    }

    public function withTimeout(int $timeout): self
    {
        $this->request_timeout = $timeout;

        return $this;
    }

    public static function cid($cid): self
    {
        return new self($cid);
    }

    public function fetch(): ?object
    {
        $client = new GuzzleHttp\Client();

        $query = str_replace(IPFS::CID_PLACEHOLDER, $this->cid, $this->ipfs);

        try {
            $response = $client->get($query, [ 'timeout' => $this->request_timeout ]);

            return (object) json_decode($response->getBody());
        } catch (Exception $e) {
            return (object) [
                'error' => [
                    'message' => "Expected data from IPFS gateway [$this->ipfs] was not found for CID [$this->cid]",
                    'query' => $query,
                ],
            ];
        }
    }
}
