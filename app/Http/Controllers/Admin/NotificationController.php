<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SimpleNotificationHelper;
use App\Models\Farmer;
use App\Models\Notification;
use App\Models\NotificationTarget;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\SendNotificationJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::with('targets')->latest()->get();
        $farmers = Farmer::all()->keyBy('id');

        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $adminController = new AdminController();
            $permissions = $adminController->getSubAdminPermissions();
            $sideMenuName = $permissions['sideMenuName'] ?? [];
            $sideMenuPermissions = $permissions['sideMenuPermissions'] ?? [];
        }

        return view('admin.notification.index', compact(
            'notifications',
            'farmers',
            'sideMenuName',
            'sideMenuPermissions'
        ));
    }


    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'farmers' => 'required|array',
            'title' => 'required|string|max:255',
            'message' => 'nullable',
        ]);

        try {
            // Save main notification record
            $notification = Notification::create([
                'user_type' => 'farmer',
                'title' => $request->title,
                'message' => $request->message,
                'is_sent' => 0,
            ]);

            foreach ($request->input('farmers', []) as $farmerId) {
                // Create target entry for each farmer
                $notification->targets()->create([
                    'targetable_id' => $farmerId,
                    'targetable_type' => \App\Models\Farmer::class,
                ]);

                // Send FCM to each farmer if they have a valid token
                $farmer = \App\Models\Farmer::find($farmerId);

                if ($farmer && $farmer->fcm_token) {
                    $cleanToken = trim($farmer->fcm_token);

                    // Skip if token is clearly invalid or too short
                    if (strlen($cleanToken) < 20) {
                        Log::warning("Skipped FCM for Farmer ID {$farmerId}: Token too short or invalid.");
                        continue;
                    }

                    try {
                        \App\Helpers\SimpleNotificationHelper::sendFcmNotification(
                            $cleanToken,
                            $request->title,
                            $request->message
                        );
                    } catch (\Exception $e) {
                        Log::error("FCM send failed for Farmer ID {$farmerId}: " . $e->getMessage());
                    }
                } else {
                    Log::warning("Farmer ID {$farmerId} has no FCM token.");
                }
            }

            return redirect()->route('notification.index')->with('success', 'Notification Sent Successfully');
        } catch (\Exception $e) {
            Log::error("Notification store failed: " . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }



    public function edit($id)
    {
        $notification = Notification::with('targets')->findOrFail($id);
        $farmers = Farmer::all();

        $selectedFarmerIds = $notification->targets
            ->where('targetable_type', \App\Models\Farmer::class)
            ->pluck('targetable_id')
            ->toArray();

        return view('admin.notification.edit', compact(
            'notification',
            'farmers',
            'selectedFarmerIds'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'farmers' => 'required|array',
        ]);

        try {
            $notification = Notification::findOrFail($id);

            $notification->update([
                'message' => $request->message,
            ]);

            $notification->targets()->delete();

            foreach ($request->input('farmers', []) as $farmerId) {
                $notification->targets()->create([
                    'targetable_id' => $farmerId,
                    'targetable_type' => \App\Models\Farmer::class,
                ]);
            }

            return redirect()->route('notification.index')->with('success', 'Notification updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error updating notification: ' . $e->getMessage());
        }
    }


    public function destroy($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();

            return redirect()->route('notification.index')->with('success', 'Notification Deleted Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting notification: ' . $e->getMessage());
        }
    }

    public function deleteAll()
{
    // Delete child targets first if foreign key constraints exist
    Notification::query()->delete(); // This will respect cascading

    return redirect()->back()->with('success', 'All notifications Deleted Successfully');
}

}
