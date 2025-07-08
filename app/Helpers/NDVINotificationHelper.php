<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\NotificationTarget;
use Google\Client;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as GuzzleClient;

class NDVINotificationHelper
{
    public static function notifyFarmer($user, $ndvi, $date, $typeId = null)
    {
        if (!$user || empty($user->fcm_token)) {
            Log::warning('NDVI Notification: Missing FCM token or user');
            return false;
        }

        $roundedNDVI = round($ndvi, 1);
        $message = "Satellite Index (NDVI): {$roundedNDVI}%, Date: $date";

        // 1. Save in notifications table
        $notification = Notification::create([
            'user_type' => 'Farmer',
            'title' => 'Result Announced - ' . date('Y', strtotime($date)),
            'message' => $message,
            'is_seen' => 0,
        ]);

        // 2. Save in notification_targets
        NotificationTarget::create([
            'notification_id' => $notification->id,
            'targetable_id' => $user->id,
            'targetable_type' => get_class($user),
        ]);

        // 3. Send FCM using existing ProductClaim helper
        return self::sendFCM($user->fcm_token, [
            'title' => "Result Announced - $date",
            'body' => $message,
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
