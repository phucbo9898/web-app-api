<?php

namespace App\Http\Controllers;

use App\Mail\ChangeEmailMail;
use App\Models\User;
use App\Models\UserDeviceToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserSettingController extends Controller
{
    public function getProfile()
    {
        $user = User::where('id', Auth::id())->first();
        if (empty($user)) {
            return $this->getResponse(false, "Account not found", 422);
        }
        return $this->getResponse(true, 'Get profile success', 200, $user);
    }
    public function changePassword(Request $request)
    {
        $currentPassword = $request->get('current_password');
        $newPassword = $request->get('new_password');
        if (!Hash::check($currentPassword, Auth::user()->password)) {
            return $this->getResponse(false, 'The current password is incorrect.', 422);
        }
        if(strcmp($currentPassword, $newPassword) == 0){
            //Current password and new password are same
            return $this->getResponse(false, "Current password and new password can not same", 422);
        }
        $statusUpdate = User::where('id', Auth::id())->update(['password' => Hash::make($newPassword)]);
        return $statusUpdate ? $this->getResponse(true, 'Change password success')
            : $this->getResponse(false, 'Change password failed', 500);
    }

    public function updateProfile(Request $request)
    {
        $getInfo = User::where('id', Auth::id())->first();
        if (empty($getInfo)) {
            return $this->getResponse(false, "Account not found", 422);
        }
        try {
            if ($request->image) {
                $image = $request->image;
                $parts = explode(";base64,", $image);
                $base64 = base64_decode($parts[1]);
                $type_aux = explode("image/", $parts[0]);
                $type = $type_aux[1];
                $uploadImage = app('firebase.firestore')->database()->collection('test')->document(Str::random(40) . '.' . $type);
                $firebase_storage_path = 'test/';
                $name = $uploadImage->id();
                if (!file_exists(public_path('firebase-temp-uploads'))) {
                    mkdir(public_path('firebase-temp-uploads'), 0770);
                }
                $localfolder = public_path('firebase-temp-uploads') .'/';
                if (file_put_contents($localfolder . $name, $base64)) {
                    $uploadedfile = fopen($localfolder.$name, 'r');
                    $bucket = app('firebase.storage')->getBucket();
                    $bucket->upload($uploadedfile, ['name' => $firebase_storage_path . $name]);
                    $firebaseStorage = app('firebase.storage');
                    $fileRef = $firebaseStorage->getBucket()->object($firebase_storage_path . $name);
                    $imageUrl = $fileRef->signedUrl(strtotime(Carbon::now()->addYear(1000)));
                    unlink($localfolder . $name);
                }

//                $fileName = Str::random(60) . '.' . $image->getClientOriginalExtension();
//                $path_upload = 'uploads';
//                Storage::disk('public')->put('images', $image);
//                $image->move(public_path($path_upload), $fileName);
            }
            User::where('id', Auth::id())->update([
//                'first_name' => $request->first_name ?? '',
//                'last_name' => $request->last_name ?? '',
                'name' => $request->first_name . ' ' . $request->last_name,
                'avatar' => $imageUrl ?? '',
                'phone' => $request->phone ?? '',
                'address' => $request->address ?? '',
            ]);

            return $this->getResponse(true, 'Change profile success', 200, $getInfo);
        } catch (\Exception $exception) {
            Log::debug($exception->getMessage());
            return $this->getResponse(false, 'Change profile failed', 500);
        }
    }

    public function updateLanguage(Request $request)
    {
        $language = $request->get('language');
        $user = User::where('id', Auth::id())->first();
        if (empty($user)) {
            return $this->getResponse(false, "Account error", 422);
        }
        User::where('id', Auth::id())->update(['setting_language' => $language]);
        return $this->getResponse(true, 'Update setting language success', 200, $user);
    }

    public function changeEmail(Request $request)
    {
        $newEmail = $request->get('email');
        $site_url = $request->get('site_url');
        $url = $request->get('url');
        $checkExistEmail = User::where('email', $newEmail)->first();
        if(!empty($checkExistEmail)){
            return $this->getResponse(false, "This email address already exists. Please enter another email address.", 422);
        }
        $user = User::where('id', Auth::id())->first();
        if (empty($user)) {
            return $this->getResponse(false, "Account error", 422);
        }
        $statusSend = $this->sendVerifyMail($newEmail, $site_url, $url, $user);

        if (!$statusSend) {
            return $this->getResponse(false, 'Send mail failed', 500);
        }

        User::where('id', Auth::id())->update(['email_temp' => $newEmail]);
        return $this->getResponse(true, 'Send mail success');
    }

    public function sendVerifyMail(string $newEmail, string $site_url, string $url, $user)
    {
        try {
            $token = $this->generateToken($user->id);

            if (empty($token)) {
                return false;
            }

            $data = [
                'url' => $site_url . '?user_id='. $user->id .'&token=' . $token,
                'user_name' => $user->name,
                'new_email' => $newEmail,
                'url_host' => $url
            ];
            Mail::to($newEmail)->queue(new ChangeEmailMail($data));
            return true;
        } catch (\Exception $exception) {
            Log::debug($exception->getMessage());
            return false;
        }
    }

    public function generateToken($user_id)
    {
        $getTokenChangeEmail = DB::table('email_change_token')->where('user_id', $user_id)->first();
        if (!empty($getTokenChangeEmail)) {
            return $getTokenChangeEmail->token;
        }

        try {
            $token = Str::random(80);
            DB::beginTransaction();

            DB::table('email_change_token')->insert([
                'user_id' => $user_id,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);

            DB::commit();
            return $token;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::debug($exception->getMessage());
            return false;
        }
    }

    public function verifyChangeEmail($id, Request $request)
    {
        $user = User::where('id', $id)->first();
        if (empty($user)) {
            return $this->getResponse(false, 'User not found', 422);
        }
        $checkExistEmail = User::where('email', $user->email_temp)->first();
        if (!empty($checkExistEmail)) {
            return $this->getResponse(false, 'This email is duplicate', 422);
        }
        $checkTokenChangeEmail = DB::table('email_change_token')->where('token', $request->get('token'))->get();
        if (count($checkTokenChangeEmail) == 0) {
            return $this->getResponse(false, 'Token not found', 422);
        }
        $newEmail = $user->email_temp;
        User::where('id', $user->id)->update([
            'email' => $newEmail,
            'email_verified_at' => Carbon::now()
        ]);

        if(Auth::check()){
            Auth::logout();
        }
        return $this->getResponse(true, 'Success verify email');
    }

    public function upload(Request $request)
    {
        try {
//            dd($request->all(), $request->image);
//            dd($request->image, $request->hasFile('image'));

            if ($request->image) {
                $image = $request->image;
                $parts = explode(";base64,", $image);
                $base64 = base64_decode($parts[1]);
                $type_aux = explode("image/", $parts[0]);
                $type = $type_aux[1];
                $uploadImage = app('firebase.firestore')->database()->collection('test')->document(Str::random(40) . '.' . $type);
                $firebase_storage_path = 'test/';
                $name = $uploadImage->id();
                $localfolder = public_path('firebase-temp-uploads') .'/';
                $file = $name. '.' . $type;
                if (file_put_contents($localfolder . $file, $base64)) {
                    $uploadedfile = fopen($localfolder.$file, 'r');
                    $bucket = app('firebase.storage')->getBucket();
                    $bucket->upload($uploadedfile, ['name' => $firebase_storage_path . $name]);
                    $firebaseStorage = app('firebase.storage');
                    $fileRef = $firebaseStorage->getBucket()->object($firebase_storage_path . $name);
                    $imageUrl = $fileRef->signedUrl(strtotime(Carbon::now()->addYear(1000)));
                }

//                $fileName = Str::random(60) . '.' . $image->getClientOriginalExtension();
//                $path_upload = 'uploads';
//                Storage::disk('public')->put('images', $image);
//                $image->move(public_path($path_upload), $fileName);
                User::where('id', 1)->update(['avatar' => $imageUrl]);
                return $this->getResponse(true, 'Upload avatar success', 200, $imageUrl);
            }
        } catch (\Exception $exception) {
            Log::debug($exception->getMessage());
        }
    }

    public function updateToken(Request $request)
    {
        try {
            $getTokenByUserLog = UserDeviceToken::where([
                'user_id' => Auth::id(),
                'device_token' => $request->token
            ])->first();
            if (empty($getTokenByUserLog)) {
                $request->user()->UserDeviceTokens()->create([
                    'device_token' => $request->token,
                    'updated_at' => Carbon::now()
                ]);
                return response()->json([
                    'message' => 'Update device token success'
                ]);
            }
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'message' => 'Update device token failed'
            ], 500);
        }
    }
}
