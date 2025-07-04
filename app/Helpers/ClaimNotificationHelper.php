<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\NotificationTarget;
use Google\Client as GoogleClient;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ClaimNotificationHelper
{
    public static function notifyFarmer($farmer, $message)
    {
        if (!$farmer || empty($farmer->fcm_token)) {
            Log::warning('Farmer or FCM token not found.');
            return false;
        }

        // 1. Save Notification in DB
        $notification = Notification::create([
            'user_type' => 'Farmer',
            'title' => 'Claim Update',
            'message' => $message,
            'is_seen' => 0
        ]);

        NotificationTarget::create([
            'notification_id' => $notification->id,
            'targetable_id' => $farmer->id,
            'targetable_type' => get_class($farmer),
        ]);

        // 2. Send FCM
        return self::sendFCM($farmer->fcm_token, [
            'title' => 'Claim Update',
            'body' => $message
        ]);
    }

    private static function getAccessToken()
    {
        try {
            $client = new \Google\Client();
            $client->setAuthConfig(storage_path('app/crop-1a4a6-firebase-adminsdk-fbsvc-2659e4d872.json'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();

            $token = $client->getAccessToken();

            if (!isset($token['access_token'])) {
                throw new \Exception('Access token not found in response.');
            }

            return $token['access_token'];
        } catch (\Exception $e) {
            Log::error('FCM Access Token Error: ' . $e->getMessage());
            return null;
        }
    }



    private static function sendFCM($token, $data)
    {
        try {
            $accessToken = self::getAccessToken();

            $client = new Client();
            $response = $client->post('https://fcm.googleapis.com/v1/projects/crop-1a4a6/messages:send', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $data['title'] ?? 'Notification',
                            'body' => $data['body'] ?? '',
                        ],
                    ],
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('FCM Send Error: ' . $e->getMessage());
            return false;
        }
    }



    public static function testNotificationByToken($token, $title, $body)
    {
        return self::sendFCM($token, [
            'title' => $title,
            'body' => $body
        ]);
    }
}
