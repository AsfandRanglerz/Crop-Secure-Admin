<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function farmerNotifications(Request $request)
    {
        $farmer = auth()->user(); // make sure farmer is authenticated via sanctum or token

        if (!$farmer || !$farmer instanceof \App\Models\Farmer) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Fetch only notifications where this farmer is a target
        $notifications = \App\Models\Notification::whereHas('targets', function ($query) use ($farmer) {
            $query->where('targetable_id', $farmer->id)
                ->where('targetable_type', \App\Models\Farmer::class);
        })->latest()->get();

        return response()->json([
            'message' => 'Notifications retrieved successfully.',
            'data' => $notifications,
        ]);
    }

    public function markAsSeen(Request $request)
    {
        $farmer = auth()->user();

        if (!$farmer || !$farmer instanceof \App\Models\Farmer) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Optional: mark specific notifications, or all
        $notificationIds = $request->input('notification_ids', []);

        $query = \App\Models\NotificationTarget::where('targetable_id', $farmer->id)
            ->where('targetable_type', \App\Models\Farmer::class);

        if (!empty($notificationIds)) {
            $query->whereIn('notification_id', $notificationIds);
        }

        $updated = $query->update(['is_read' => 1]);

        return response()->json([
            'message' => 'Notifications marked as seen.',
            'updated' => $updated,
        ]);
    }
}
