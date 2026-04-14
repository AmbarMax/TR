<?php

namespace App\Web3;

use App\Web3\Packages\web3\Contract;
use App\Web3\Packages\web3\Web3;
use Illuminate\Support\Facades\Log;
use App\Web3\Packages\EthereumTx\Transaction;

final class TrophyNFT
{
    private const CREATE_CATEGORY = 'createCategory';

    public function __construct(
        public readonly Web3      $web3,
        public readonly array         $abi,
        public readonly Address       $address,
        private readonly Contract $contract,
        public readonly Address       $ownerAddress,
        public readonly PrivateKey    $ownerPK,
        public readonly Address       $signerAddress,
        public readonly PrivateKey    $signerPK,
        public readonly int           $gas,
        public readonly int           $chainId = 56,
    )
    {
    }

    public function create(string $pinataUrl, string $categoryId, int $maxSupply): void
    {
        $data = '0x' . $this->contract->getData(
                self::CREATE_CATEGORY,
                $categoryId,
                $maxSupply,
                $pinataUrl
            );

        $transaction = new Transaction([
            'from' => $this->ownerAddress->value,
            'to' => $this->address->value,
            'gasPrice' => $this->gasPrice(),
            'gas' => $this->gas,
            'value' => '0x0',
            'data' => $data,
            'chainId' => $this->chainId,
            'nonce' => Nonce::get($this->web3->eth, $this->ownerAddress),
        ]);

        $signedTransaction = $transaction->sign($this->ownerPK->value);

//        try {
            $this->contract->eth->sendRawTransaction(
                '0x' . $signedTransaction,
                function ($err, $tx) use ($categoryId, $pinataUrl, $maxSupply) {
                    if (!$err) {
                        return;
                    }
                    $errorMessage = [];
                    $expectedNonce = [];
                    if (preg_match("/.+ Nonce too low. Expected nonce to be (d+) .+/", $err->getMessage(), $expectedNonce)) {
                        Nonce::set($expectedNonce[1], $this->address);
                        $this->create($pinataUrl, $categoryId, $maxSupply);
                        return;
                    } elseif (preg_match("/.+ reverted with reason string \'(.+)\'/", $err->getMessage(), $errorMessage)) {
                        Log::error($err->getMessage());
                        throw new \InvalidArgumentException($errorMessage[1]);
                    }
                    Log::error("Create NFT trophy error: $err");
                    throw new \InvalidArgumentException($err);
                }
            );
//        } catch (\Exception $e) {
//            if (preg_match("/Nonce too low. Expected nonce to be (d+) .+/", $e->getMessage(), $expectedNonce)) {
//                Nonce::set($expectedNonce[1], $this->address);
//                $this->create($pinataUrl, $categoryId, $maxSupply);
//                return;
//            } elseif (preg_match("/.+ reverted with reason string \'(.+)\'/", $e->getMessage(), $errorMessage)) {
//                Log::error($e->getMessage());
//                throw new \InvalidArgumentException($errorMessage[1]);
//            } else {
//                Log::error("Create NFT trophy error: $e");
//                throw new \InvalidArgumentException($e);
//            }
//        }
    }

    public function gasPrice(): int
    {
        $this->web3->getEth()->gasPrice(function ($err, $price) use (&$gasPrice) {
            if ($err !== null) {
                Log::error($err->getMessage());
                throw new \InvalidArgumentException($err);
            }
            $gasPrice = $price->toString();
        });

        return (int)$gasPrice;
    }

    public function checkOwnership(string $tokenId, string $owner): bool
    {
        $isOwner = false;

        $this->contract->call("ownerOf", $tokenId, function ($err, $result) use ($owner, &$isOwner) {
            if ($err !== null) {
                Log::error($err->getMessage());
                throw new \InvalidArgumentException($err);
            }
            $isOwner = strtolower($result[0]) === strtolower($owner);
        });

        return $isOwner;
    }
}