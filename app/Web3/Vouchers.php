<?php

namespace App\Web3;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final readonly class Vouchers
{
    public function createVoucher(string $categoryId, Address $senderAddress): Voucher
    {
        $client = new Client();
        $url = 'http://localhost:3000/create-voucher'; // Replace with your Node.js server URL

        try {
            $response = $client->request('POST', $url, [
                'json' => [
                    'categoryId' => $categoryId,
                    'userAddress' => $senderAddress->value
                ]
            ]);

            $body = $response->getBody();
            $data = json_decode($body, true);

            if ($response->getStatusCode() != 200) {
                throw new \InvalidArgumentException('Error creating voucher: ' . $body);
            }

            return new Voucher($categoryId, $data['signature']);
        } catch (GuzzleException $e) {
            throw new \InvalidArgumentException('Error creating voucher: ' . $e->getMessage());
        }
    }
}