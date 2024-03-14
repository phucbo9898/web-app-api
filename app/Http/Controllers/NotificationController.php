<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Slide;
use App\Models\User;
use App\Notifications\SendPushNotification;
use App\Services\FirebasePushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\FcmOptions;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kutia\Larafirebase\Services\Larafirebase;
use Notification;


class NotificationController extends Controller
{
    protected $notification;
    protected $firebasePushNotificationService;

    public function __construct(FirebasePushNotificationService $firebasePushNotificationService)
    {
        $this->firebasePushNotificationService = $firebasePushNotificationService;
    }

    public function notification(Request $request)
    {
        try {
            $this->firebasePushNotificationService->sendNotification();
            return response()->json([
                'message' => 'Send notification success',
            ]);
        } catch (\Exception $e) {
            report($e);
            Log::debug($e->getMessage());
            return response()->json([
                'message' => 'Send notification success',
            ], 500);
        }
    }
}
