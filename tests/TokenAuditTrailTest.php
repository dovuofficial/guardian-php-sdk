<?php


use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Trustchain\IPFS;
use Dovu\GuardianPhpSdk\Trustchain\Mirrornode;
use Dovu\GuardianPhpSdk\Workflow\Constants\WorkflowTask;
use Dovu\GuardianPhpSdk\Workflow\GuardianWorkflowConfiguration;

describe('A test suite to match tokens that are minted to data', function () {

    /**
     * If policy is in DRYRUN state this process should not happen.
     *
     * Clients of this flow are expected to have a toble/state that:
     * - Links metadata to a known key (uuid)
     * - A table that can link a serial to metadata
     * - Link a trustchain to a particular serial/s
     * - Link the schemas to a particular serial/s
     */
    it('Should be able to fetch token nft data from mirrornode', function () {

        // Target UUID (not needed -- as it can be reconciled later, in client usage)
        $claim_uuid = "6c5c61bd-f1fd-4b04-834b-c068dd5bdeae";
        $token_id = "0.0.4884916";

        $credits = Mirrornode::credits($token_id)
            ->forTestnet()
//            ->fromSerial(1) // Used after serials have been processed in system
            ->fetch();

        /**
         * A client would be expected to iterate through each of the nfts in class
         */
        $credit_one = $credits->nfts[1];

        // Get Metadata for credit, decoded
        $metadata_ts = base64_decode($credit_one->metadata);

        $topic_msg = Mirrornode::message($metadata_ts)
            ->forTestnet()
            ->fetch();

        $message = json_decode(base64_decode($topic_msg->message));

        $cid = $message->cid;

        $vp = IPFS::cid($cid)->fetch();

        expect(array_key_exists('error', (array) $vp))->toBeFalsy();

        // We should scan each of the vp -> vcs -> subjects for targets (or first extract with schema ids)
        $subject = $vp->verifiableCredential[0]->credentialSubject[0];

        // This is optional here
        expect($subject->uuid)->toBe($claim_uuid);

        // Example structure of connecting metadata (ts) to serials through a predefined key.
        $state = [
            'metadata' => $metadata_ts,
            'serial_example' => $credit_one->serial_number,
            'linked_key' => $subject->uuid,
        ];

        ray($state);
    });

})->with();
