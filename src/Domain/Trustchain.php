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
        return [
            "type" => $this->ensureItemType(),
            "proof" => $this->vc_doc->proof,
            "title" => $this->item->title,
            "issuer" => [
                "did" => $this->item->issuer,
                "username" => $this->item->username,
            ],
            "visible" => $this->item->visible,
            "messageId" => $this->document->messageId,
            "createDate" => $this->document->createDate,
            "updateDate" => $this->document->updateDate,
            "description" => $this->item->description,
            "issuanceDate" => $this->vc_doc->issuanceDate,
        ];
    }
}

class Trustchain
{
    private object $trustchain;

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
                "name" => $policy->username,
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

        return [
            "hash" => $document->hash,
//            "tokenId" => $document->hash,
//            "topicId" => $document->hash,
//            "mintDate" => $document->hash,
            "messageIds" => $inner->messageIds, // Instead of messageId
            "createDate" => $inner->createDate,
            "updateDate" => $inner->updateDate,
            "mintAmount" => 1, // TODO: Resolve
            "issuer" => $this->issuer(),
            "trustchain" => $this->trustchain(),
            "policy" => $this->policy(),
            // TODO: check if proof is required
//             "proof" => null,
        ];
    }
}
