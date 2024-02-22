<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\SendPushNotification;
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

    public function __construct()
    {
        $this->notification = Firebase::messaging();
    }

    public function updateToken(Request $request)
    {
        try {
            $request->user()->update(['device_token' => $request->token]);
            return response()->json([
                'message' => 'Update device token success'
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'message' => 'Update device token failed'
            ], 500);
        }
    }

    public function notification(Request $request)
    {
//        dd(Auth::user()->device_token);
        $request->validate([
            'title' => 'required',
            'message' => 'required'
        ]);

        try {
            $deviceTokens = User::whereNotNull('device_token')->distinct('device_token')->pluck('device_token')->toArray();
            $title = $request->input('title');
            $body = $request->input('message');

            $messages = [];
            foreach ($deviceTokens as $deviceToken) {
                $messages[] = [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSoVki-W_uujCaTvpNM11TDow7Quak0v3sC-4HKViNS4pdPnqUdydTBFn0TQunXiPzQOUM&usqp=CAU',
                        "url" => 'https://www.google.com'
                    ],
                    'data' => [
                        "redirect_to" => 'about'
                    ]
                ];
            }

//            dd($messages);
//            $message = CloudMessage::fromArray([
////                'token' => $fcmTokens[0],
//                'notification' => [
//                    'title' => '$GOOG up 1.43% on the day',
//                    'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
//                    'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSoVki-W_uujCaTvpNM11TDow7Quak0v3sC-4HKViNS4pdPnqUdydTBFn0TQunXiPzQOUM&usqp=CAU',
//                ]
//            ]);

//            $sendReport = $this->notification->sendMulticast($message, $fcmTokens);
            $this->notification->sendAll($messages);
//            dd($sendReport->getItems());


//            $fcmTokens = User::whereNotNull('device_token')->pluck('device_token')->toArray();
//
//            \Notification::send(null,new SendPushNotification($request->title,$request->message,$fcmTokens));
//
//            /* or */
//
//            //auth()->user()->notify(new SendPushNotification($title,$message,$fcmTokens));
//
//            /* or */
////            Larafirebase::withTitle($request->title)
////                ->withBody($request->message)
////                ->withClickAction("https://www.google.com")
////                ->withIcon("https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSoVki-W_uujCaTvpNM11TDow7Quak0v3sC-4HKViNS4pdPnqUdydTBFn0TQunXiPzQOUM&usqp=CAU")
////                ->sendMessage($fcmTokens);
//
            $data = [
                'title' => $title,
                'body' => $body,
                'link' => '/about'
            ];
            return response()->json([
                'message' => 'Send notification success',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            report($e);
            Log::debug($e->getMessage());
            return response()->json([
                'message' => 'Send notification success',
            ], 500);
        }
    }
}
