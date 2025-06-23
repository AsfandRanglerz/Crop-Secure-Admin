<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SimpleNotificationHelper;
use App\Models\Farmer;
use App\Models\Notification;
use App\Models\NotificationTarget;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
            'user_type' => 'required|array',
            'message' => 'required|string',
        ]);

        try {
            $notification = Notification::create([
                'user_type' => implode(',', $request->user_type),
                'message' => $request->message,
                'is_sent' => 0,
            ]);

            foreach ($request->input('farmers', []) as $farmerId) {
                $notification->targets()->create([
                    'targetable_id' => $farmerId,
                    'targetable_type' => Farmer::class,
                ]);

                $farmer = Farmer::find($farmerId);
                if ($farmer && $farmer->device_token) {
                    SimpleNotificationHelper::sendFcmNotification(
                        $farmer->device_token,
                        'Crop Secure Alert',
                        $request->message,
                        ['notification_id' => (string)$notification->id]
                    );
                }
            }

            return redirect()->route('notification.index')->with('success', 'Notification saved and FCM sent.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $notification = Notification::with('targets')->findOrFail($id);
        $farmers = Farmer::all();

        $selectedFarmerIds = $notification->targets
            ->where('targetable_type', Farmer::class)
            ->pluck('targetable_id')
            ->toArray();

        $selectedUserTypes = explode(',', $notification->user_type);

        return view('admin.notification.edit', compact(
            'notification',
            'farmers',
            'selectedFarmerIds',
            'selectedUserTypes'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_type' => 'required|array',
            'message' => 'required|string',
        ]);

        try {
            $notification = Notification::findOrFail($id);

            $notification->update([
                'user_type' => implode(',', $request->user_type),
                'message' => $request->message,
            ]);

            $notification->targets()->delete();

            foreach ($request->input('farmers', []) as $farmerId) {
                $notification->targets()->create([
                    'targetable_id' => $farmerId,
                    'targetable_type' => Farmer::class,
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

            return redirect()->route('notification.index')->with('success', 'Notification deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting notification: ' . $e->getMessage());
        }
    }
}
