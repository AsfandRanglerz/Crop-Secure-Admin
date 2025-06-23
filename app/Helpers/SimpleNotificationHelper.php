<?php

namespace App\Helpers;

use Google\Client;
use Illuminate\Support\Facades\Log;

class SimpleNotificationHelper
{
    private static function getGoogleAccessToken()
    {
        try {
            $credentialsPath = storage_path(env('FIREBASE_CREDENTIALS', 'app/crop-1a4a6-firebase-adminsdk-fbsvc-2659e4d872.json'));

            $client = new Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/cloud-platform');
            $accessToken = $client->fetchAccessTokenWithAssertion();

            if (isset($accessToken['error'])) {
                throw new \Exception('Error fetching access token: ' . $accessToken['error_description']);
            }

            return $accessToken['access_token'];
        } catch (\Exception $e) {
            Log::error('Firebase Auth Error: ' . $e->getMessage());
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

            $url = 'https://fcm.googleapis.com/v1/projects/' . env('FIREBASE_PROJECT_ID', 'crop-1a4a6') . '/messages:send';

            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ];

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($message),
                CURLOPT_HTTPHEADER => $headers,
            ]);

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                throw new \Exception('CURL Error: ' . curl_error($curl));
            }

            $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpStatus !== 200) {
                throw new \Exception('FCM failed with status ' . $httpStatus . ': ' . $response);
            }

            Log::info('FCM Success: ' . $response);
            return json_decode($response, true);
        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
