<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\NotificationTarget;
use Google\Client;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as GuzzleClient;

class ProductClaimNotificationHelper
{
    public static function notifyFarmer($farmer, $message)
    {
        if (!$farmer || empty($farmer->fcm_token)) {
            Log::warning('ProductClaim: FCM token not found.');
            return false;
        }

        // Save notification
        $notification = Notification::create([
            'user_type' => 'Farmer',
            'title' => 'Order Update',
            'message' => $message,
            'is_seen' => 0
        ]);

        NotificationTarget::create([
            'notification_id' => $notification->id,
            'targetable_id' => $farmer->id,
            'targetable_type' => get_class($farmer),
        ]);

        // Send FCM
        return self::sendFCM($farmer->fcm_token, [
            'title' => 'Order Update',
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

            $client = new GuzzleClient(); // ← Use Guzzle here

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
            Log::error('ProductClaim FCM Send Error: ' . $e->getMessage());
            return false;
        }
    }
}
