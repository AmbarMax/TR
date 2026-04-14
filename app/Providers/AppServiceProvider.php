<?php

namespace App\Providers;

use App\Web3\Address;
use App\Web3\KeyNFT;
use App\Web3\Pinata;
use App\Web3\PrivateKey;
use App\Web3\TrophyNFT;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Web3\Packages\web3\Web3;
use App\Web3\Packages\web3\Contract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TrophyNFT::class, function (Application $app) {
            return new TrophyNFT(
                web3: $web3 = new Web3((string)env('BLOCKCHAIN_PROVIDER_URL')),
                abi: $abi = json_decode(file_get_contents(config_path('trophy/trophyNFT.json')), true),
                address: $address = Address::from(env('TROPHY_NFT_ADDRESS')),
                contract: (new Contract($web3->provider, $abi))->at($address->value),
                ownerAddress: Address::from(env('OWNER_ADDRESS')),
                ownerPK: PrivateKey::from(env('OWNER_PRIVATE_KEY')),
                signerAddress: Address::from(env('SIGNER_ADDRESS')),
                signerPK: PrivateKey::from(env('SIGNER_PRIVATE_KEY')),
                gas: intval(env('ETH_GAS', 10 * 231931)),
                chainId: env('CHAIN_ID', 56),
            );
        });

        $this->app->bind(KeyNFT::class, function (Application $app) {
            return new KeyNFT(
                web3: $web3 = new Web3((string)env('BLOCKCHAIN_PROVIDER_URL')),
                abi: $abi = json_decode(file_get_contents(config_path('key/keyNFT.json')), true),
                address: $address = Address::from(env('KEY_NFT_ADDRESS')),
                contract: (new Contract($web3->provider, $abi))->at($address->value),
                ownerAddress: Address::from(env('OWNER_KEY_ADDRESS')),
                ownerPK: PrivateKey::from(env('OWNER_KEY_PRIVATE_KEY')),
                signerAddress: Address::from(env('SIGNER_ADDRESS')),
                signerPK: PrivateKey::from(env('SIGNER_PRIVATE_KEY')),
                gas: intval(env('ETH_GAS', 10 * 231931)),
                chainId: env('CHAIN_ID', 56),
            );
        });

        $this->app->bind(Pinata::class, function (Application $app) {
            return new Pinata(
                apiKey: env('PINATA_API_KEY'),
                secretKey: env('PINATA_API_SECRET'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::macro('image', fn ($asset) => $this->asset("resources/assets/images/$asset"));
        Vite::macro('svg', fn ($asset) => $this->asset("resources/assets/svg/$asset"));
        Vite::macro('apiMail', fn ($asset) => $this->asset("resources/views/api/mail/images/$asset"));
        Vite::macro('adminImage', fn ($asset) => $this->asset("resources/admin/images/$asset"));
    }
}
