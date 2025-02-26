<?php

namespace Dovu\GuardianPhpSdk\Domain;

class TrustchainItem
{
    /**
     * These two items below detect, if a type is incorrect, as in, if it is a policy event
     * that represents a mint of an asset - If it is detected, it will modify accordingly,
     * based on what the front-end is expecting.
     */
    public const TYPE_POLICY = "POLICY";

    public const TYPE_MODIFIER = "mint";

    /**
     * This relates to the overarching structure of a particular VC in a trustchain item,
     * and describes the top of level attributes, such as title, issuer and username.
     *
     * @var object
     */
    private object $item;

    /**
     * This property relates to the document related to the top-level information of a
     * particular VC So would include information related to general block data for a
     * context.
     *
     * @var object
     */
    private object $document;

    /**
     * This VC Document includes more information related to the credential subject, and
     * also includes the proof that is signed through a particular identity.
     *
     * @var object
     */
    private object $vc_doc;

    /**
     * @param array $element
     */
    public function __construct(array $element)
    {
        $this->item = (object) $element;
        $this->document = (object) $this->item->document;
        $this->vc_doc = (object) $this->document->document;
    }

    public static function itemFormat($element): array
    {
        return (new self($element))->format();
    }

    private function ensureItemType(): string
    {
        $type = $this->document->type;

        return $type == self::TYPE_POLICY ? self::TYPE_MODIFIER : $type;
    }

    public function format(): array
    {
        $format = [
            "type" => $this->ensureItemType(),
            "proof" => $this->vc_doc->proof,
            "title" => $this->item->title,
            "issuer" => [
                "did" => $this->item->issuer,
                "username" => $this->item->username,
            ],
            "visible" => $this->item->visible,
            "createDate" => $this->document->createDate,
            "updateDate" => $this->document->updateDate,
            "description" => $this->item->description,
            "issuanceDate" => $this->vc_doc->issuanceDate,
        ];

        if (array_key_exists('messageId', (array) $this->document)) {
            $format["messageId"] = $this->document->messageId;
        }

        return $format;
    }
}

class Trustchain
{
    private object $trustchain;

    private ?string $token_id = null;

    private ?string $mint_rule = null;

    public function __construct(CredentialDocumentBlock $documentBlock)
    {
        $this->trustchain = (object) $documentBlock->getBlockData();
    }

    public function issuer(): array
    {
        $document = (object) $this->trustchain->vcDocument;

        return [
            "did" => $document->issuer,
            "username" => $document->username,
        ];
    }

    public function policy(): array
    {
        $policy = (object) $this->trustchain->policyDocument;
        $document = (object) $policy->document;

        return [
            "id" => $document->id,
            "name" => $policy->name,
            "issuer" => [
                "did" => $policy->issuer,
                "username" => $policy->username,
            ],
            "version" => $policy->version,
            "createDate" => $document->createDate,
            "updateDate" => $document->updateDate,
            "description" => $policy->description,
        ];
    }

    public function trustchain(): array
    {
        $documents = $this->trustchain->documents;

        return array_map(function ($elem) {
            return TrustchainItem::itemFormat($elem);
        }, array_reverse($documents));
    }

    public function withToken(string $token_id): self
    {
        $this->token_id = $token_id;

        return $this;
    }

    public function withMintRule(string $mint_rule): self
    {
        $this->mint_rule = $mint_rule;

        return $this;
    }

    /**
     * TODO: This requires more work as I'm not convinced that there are mappings between
     * the current That I am retrieving versus other datasets, especially around token and
     * mint dates.
     *
     * Next try with published testnet.
     *
     * @return array
     */
    public function format()
    {
        $document = (object) $this->trustchain->vcDocument;
        $inner = (object) $document->document;
        $subject = (object) $inner->document;

        $claim = $subject->credentialSubject[0];
        $amount = array_key_exists($this->mint_rule, $claim) ? $claim[$this->mint_rule] : null;

        return [
            "hash" => $document->hash,
            "tokenId" => $this->token_id,
            "topicId" => $inner->topicId,
            "mintDate" => $inner->updateDate,
            "messageIds" => $inner->messageIds,
            "createDate" => $inner->createDate,
            "updateDate" => $inner->updateDate,
            "mintAmount" => $amount,
            "issuer" => $this->issuer(),
            "trustChain" => $this->trustchain(),
            "policy" => $this->policy(),
            "proof" => $subject->proof,
        ];
    }
}
