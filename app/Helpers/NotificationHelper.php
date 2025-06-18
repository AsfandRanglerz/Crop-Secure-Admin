<?php

namespace App\Helpers;

use Google\Client;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    private static function getGoogleAccessToken()
    {
        try {
            $client = new Client();
            $client->setAuthConfig(storage_path('app/crop-1a4a6-firebase-adminsdk-fbsvc-2659e4d872.json')); // Update to your actual file name
            $client->addScope('https://www.googleapis.com/auth/cloud-platform');
            $accessToken = $client->fetchAccessTokenWithAssertion();

            if (isset($accessToken['error'])) {
                throw new \Exception('Error fetching access token: ' . $accessToken['error_description']);
            }

            return $accessToken['access_token'];
        } catch (\Exception $e) {
            Log::error('Google Access Token Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function sendFcmNotification($deviceToken, $title, $body, $data = [])
    {
        try {
            $accessToken = self::getGoogleAccessToken();

            $message = [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_map('strval', $data),
                ],
            ];

            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://fcm.googleapis.com/v1/projects/crop-1a4a6/messages:send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($message),
                CURLOPT_HTTPHEADER => $headers,
            ]);

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                throw new \Exception('cURL error: ' . curl_error($curl));
            }

            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($statusCode !== 200) {
                throw new \Exception('FCM failed with status ' . $statusCode . ': ' . $response);
            }

            Log::info('FCM sent: ' . $response);
        } catch (\Exception $e) {
            Log::error('FCM Notification Error: ' . $e->getMessage());
        }
    }
}
