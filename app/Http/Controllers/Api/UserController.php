<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;

class UserController extends Controller
{
    public $successStatus = 200;
    public $errorStatus = 500;

    public function login()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();

            $success['token'] =  $user->createToken('nApp')->accessToken;
            $success['role'] =  $user->role;
            $success['name'] =  $user->name;
            $success['user_id'] =  $user->id;

            return response()->json(['success' => $success], $this->successStatus);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function updateToken(Request $request)
    {
        auth()->user()->update(['fcm' => $request->token]);

        return response()->json(['Token successfully stored.']);
    }

    public function createTeam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $userCek = Auth::user();

        if ($userCek->role != 'admin') {
            return response()->json(['message' => 'Your token is not admin'], $this->errorStatus);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['role'] = 'team';

        $user = User::create($input);

        return response()->json(['success' => $user], $this->successStatus);
    }

    public function logout(Request $request)
    {
        $logout = $request->user()->token()->revoke();
        if ($logout) {
            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        }
    }

    public function userList()
    {
        $user = User::where('role', 'team')->get();

        $dataUser = array();
        foreach ($user as $us) {
            array_push($dataUser, [
                'id' => $us->id,
                'name' => $us->name,
                'email' => $us->email
            ]);
        }

        return response()->json(['success' => $dataUser], $this->successStatus);
    }

    public function userDetail()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }

    public function userDelete(Request $request)
    {
        $user = Auth::user();

        if ($user->role != 'admin') {
            return response()->json(['message' => 'Your token is not admin'], $this->errorStatus);
        }

        if ($user->id == $request->user_id) {
            return response()->json(['message' => 'Your request is admin id not allowed'], $this->errorStatus);
        }

        $userDel = User::findOrFail($request->user_id);
        $userDel->delete();

        return response()->json(['message' => 'Successfully deleted user'], $this->successStatus);
    }

    public function sendPushNotification()
    {
        $user = Auth::user();

        if ($user->role != 'admin') {
            return response()->json(['message' => 'Your token is not admin'], $this->errorStatus);
        }

        $dataSchedule = Schedule::all();

        $contentNotif['title'] = '';
        $contentNotif['body'] = '';
        $contentNotif['vibrate'] = 1;
        $contentNotif['sound'] = 1;

        foreach ($dataSchedule as $s) {
            $contentNotif["title"] .= $s->name;
            $contentNotif["body"] .= $s->description;
        }

        $url = 'https://fcm.googleapis.com/fcm/send';
        $FcmToken = User::whereNotNull('fcm')->pluck('fcm')->all();

        $serverKey = 'AAAACuoJzk8:APA91bFIopbddvA0c_n0IpgRQmQ3taw_7xpHAjb484eH8gKaYsFcaCBMwRMi7rJAp6-9COsZMkyL-ZduBmzUX7eq1kD72viTo-QJTfJRVSYdb3U7Y1zc-9MZ5lIw8K0I0_tt9gH5XgCG';

        $data = [
            "registration_ids" => $FcmToken,
            "notification" => $contentNotif
        ];

        $encodedData = json_encode($data);

        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

        // Execute post
        $result = curl_exec($ch);

        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);

        // FCM response
        return response(['message' => json_decode($result, true)]);
    }
}
