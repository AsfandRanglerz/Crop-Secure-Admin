<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\NotificationTarget;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;

class WeatherNotificationHelper
{
    public static function notifyFarmer($user, $message)
    {
        if (!$user || empty($user->fcm_token)) {
            Log::warning('🌐 WeatherNotification: User invalid or FCM token missing. User ID: ' . optional($user)->id);
            return false;
        }

        Log::info('📬 Sending weather notification to User ID: ' . $user->id);

        // 1. Save in notifications table
        $notification = Notification::create([
            'user_type' => 'Farmer',
            'title' => 'Weather Alert',
            'message' => $message,
            'is_seen' => 0,
        ]);

        // 2. Save in notification_targets
        NotificationTarget::create([
            'notification_id' => $notification->id,
            'targetable_id' => $user->id,
            'targetable_type' => get_class($user),
        ]);

        // 3. Send FCM
        return self::sendFCM($user->fcm_token, [
            'title' => 'Weather Alert',
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
            Log::error('🔐 FCM Access Token Error: ' . $e->getMessage());
            return null;
        }
    }

    private static function sendFCM($token, $data)
    {
        try {
            $accessToken = self::getAccessToken();
            if (!$accessToken) {
                Log::error('❌ FCM Access Token is null');
                return false;
            }

            $client = new GuzzleClient();

            $response = $client->post('https://fcm.googleapis.com/v1/projects/crop-1a4a6/messages:send', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $data['title'] ?? 'Weather Notification',
                            'body' => $data['body'] ?? '',
                        ],
                    ],
                ],
            ]);

            Log::info('✅ FCM Response: ' . $response->getBody());
            return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            Log::error('🚨 FCM Send Error: ' . $e->getMessage());
            return false;
        }
    }
}
