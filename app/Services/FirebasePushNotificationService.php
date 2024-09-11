<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebasePushNotificationService extends BaseService
{
    protected $notification;

    public function __construct()
    {
        // With package kreait/firebase-laravel
        $this->notification = Firebase::messaging();

        // With package kreait/firebase-php
//        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/'.env('FIREBASE_FCM_CREDENTIALS'));
//        $firebase = (new Factory())
//            ->withServiceAccount($serviceAccount);
//        $this->notification = $firebase->createMessaging();
    }

    public function sendNotification(array $userIds, $title, $body, $urlRedirectDetail)
    {
        $messages = [];
        try {
            $lstDeviceTokenByUsers = User::whereIn('user_id', $userIds)->where('user_id', '<>', Auth::id())->distinct('device_token')->whereNotNull('device_token')->whereNull('deleted_at')->get();
            foreach ($lstDeviceTokenByUsers as $user) {
                $messages[] = [
                    'token' => $user['device_token'],
                    'notification' => null,
                    'data' => [
                        "redirect_to" => $urlRedirectDetail,
//                        "unread_count" => $unreadCountByUser,
                        'title' => $title,
                        'body' => $body,
                    ]
                ];
            }
            $this->notification->sendAll($messages);
        } catch (\Exception $exception) {
            Log::debug($exception->getMessage());
        }
    }
}
