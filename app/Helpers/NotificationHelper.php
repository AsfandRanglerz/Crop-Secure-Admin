<?php

namespace App\Helpers;

use Google\Client;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as GuzzleClient;

class NotificationHelper
{

    public static function sendFcmNotification($token, $title, $body, array $data = [])
    {
        try {
            $accessToken = self::getAccessToken();

            if (!$accessToken) {
                Log::error('❌ Access token not retrieved.');
                return false;
            }

            $client = new \GuzzleHttp\Client();

            $message = [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
            ];

            if (!empty($data)) {
                $message['data'] = array_map('strval', $data);
            }

            $payload = ['message' => $message];

            $response = $client->post('https://fcm.googleapis.com/v1/projects/crop-1a4a6/messages:send', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            return json_decode($response->getBody(), true);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('Client Error Response: ' . $e->getResponse()->getBody()->getContents());
            return false;
        } catch (\Exception $e) {
            Log::error('General Error: ' . $e->getMessage());
            return false;
        }
    }

    // public static function sendFcmNotification($token, $title, $body, array $data = [])
    // {
    //     try {
    //         $accessToken = self::getAccessToken();
    //         // dd($accessToken, '🔑 Access Token');

    //         if (!$accessToken) {
    //             dd('❌ Access token not retrieved.');
    //         }

    //         $client = new \GuzzleHttp\Client();

    //         $message = [
    //             'token' => $token,
    //             'notification' => [
    //                 'title' => $title,
    //                 'body' => $body,
    //             ],
    //         ];

    //         if (!empty($data)) {
    //             $message['data'] = array_map('strval', $data); // FCM v1 requires string values
    //         }

    //         $payload = ['message' => $message];
    //         // dd($payload, '📦 Payload Sent to FCM');

    //         $response = $client->post('https://fcm.googleapis.com/v1/projects/crop-1a4a6/messages:send', [
    //             'headers' => [
    //                 'Authorization' => 'Bearer ' . $accessToken,
    //                 'Content-Type' => 'application/json',
    //             ],
    //             'json' => $payload,
    //         ]);

    //         $responseBody = json_decode($response->getBody(), true);
    //         dd($response->getStatusCode(), $responseBody, '✅ FCM Response');

    //         return $responseBody;
    //     } catch (\GuzzleHttp\Exception\ClientException $e) {
    //         dd('🛑 Client Error', $e->getResponse()->getBody()->getContents());
    //     } catch (\Exception $e) {
    //         dd('🔥 General Exception', $e->getMessage());
    //     }
    // }



    private static function getAccessToken()
    {
        try {
            $client = new Client();
            $client->setAuthConfig(storage_path('app/crop-1a4a6-firebase-adminsdk-fbsvc-2659e4d872.json'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();

            return $client->getAccessToken()['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error('FCM AccessToken Error: ' . $e->getMessage());
            return null;
        }
    }
}
