<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function farmerNotifications(Request $request)
    {
        $farmer = auth()->user();

        if (!$farmer || !$farmer instanceof \App\Models\Farmer) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Pagination setup
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('limit', 10);
        $offset = ($page - 1) * $perPage;

        // Query builder for farmer's notifications
        $query = \App\Models\Notification::whereHas('targets', function ($q) use ($farmer) {
            $q->where('targetable_id', $farmer->id)
                ->where('targetable_type', \App\Models\Farmer::class);
        });

        $total = $query->count();

        // Fetch paginated results with targets
        $notifications = $query->with(['targets' => function ($q) use ($farmer) {
            $q->where('targetable_id', $farmer->id)
                ->where('targetable_type', \App\Models\Farmer::class);
        }])
            ->latest()
            ->offset($offset)
            ->limit($perPage)
            ->get();

        return response()->json([
            'message' => 'Notifications retrieved successfully.',
            'data' => $notifications,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $perPage,
                'last_page' => ceil($total / $perPage),
            ],
        ]);
    }



    public function markAsSeen($id)
    {
        $farmer = auth()->user();

        if (!$farmer || !$farmer instanceof \App\Models\Farmer) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notification = \App\Models\Notification::where('id', $id)
            ->whereHas('targets', function ($query) use ($farmer) {
                $query->where('targetable_id', $farmer->id)
                    ->where('targetable_type', \App\Models\Farmer::class);
            })
            ->first();

        if (!$notification) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        if ($notification->is_seen == 0) {
            $notification->is_seen = 1;
            $notification->save();
        }

        return response()->json([
            'message' => 'Notification marked as seen.',
            'data' => $notification,
        ]);
    }

    public function clearFarmerNotifications(Request $request)
    {
        $farmer = auth()->user();

        if (!$farmer || !$farmer instanceof \App\Models\Farmer) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Get all notification_target entries for this farmer
        $targets = \App\Models\NotificationTarget::where('targetable_id', $farmer->id)
            ->where('targetable_type', \App\Models\Farmer::class)
            ->get();

        $notificationIds = $targets->pluck('notification_id')->toArray();

        // Delete only the target records (unassigns notifications)
        \App\Models\NotificationTarget::whereIn('id', $targets->pluck('id'))->delete();

        // Optional: delete notifications if no other targets are assigned
        \App\Models\Notification::whereIn('id', $notificationIds)
            ->doesntHave('targets') // if no other target exists
            ->delete();

        return response()->json([
            'message' => 'All notifications cleared for this farmer.',
        ]);
    }

    public function unseenFarmerNotifications(Request $request)
    {
        $farmer = auth()->user();

        if (!$farmer || !$farmer instanceof \App\Models\Farmer) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Pagination (optional)
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('limit', 10);
        $offset = ($page - 1) * $perPage;

        // Notifications that are unseen for this farmer
        $query = \App\Models\Notification::where('is_seen', 0)
            ->whereHas('targets', function ($query) use ($farmer) {
                $query->where('targetable_id', $farmer->id)
                    ->where('targetable_type', \App\Models\Farmer::class);
            });

        $total = $query->count();

        $notifications = $query->with(['targets' => function ($q) use ($farmer) {
            $q->where('targetable_id', $farmer->id)
                ->where('targetable_type', \App\Models\Farmer::class);
        }])
            ->latest()
            ->offset($offset)
            ->limit($perPage)
            ->get();

        return response()->json([
            'message' => 'Unseen notifications retrieved successfully.',
            'data' => $notifications,
        ]);
    }
}
