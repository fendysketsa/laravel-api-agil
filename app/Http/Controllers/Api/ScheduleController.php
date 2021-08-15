<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Validator;

class ScheduleController extends Controller
{
    public $successStatus = 200;
    public $errorStatus = 500;

    public function userListSchedule()
    {
        $schedule = Schedule::where('posted_at', '>', DATE(now()))->get();

        $dataSchedule = array();
        foreach ($schedule as $us) {
            array_push($dataSchedule, [
                'id' => $us->id,
                'name' => $us->name,
                'description' => $us->description,
                'posted_at' => $us->posted_at,
            ]);
        }

        return response()->json(['success' => $dataSchedule], $this->successStatus);
    }

    public function createSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:schedules,name',
            'description' => 'required',
            'posted_at' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $userCek = Auth::user();

        if ($userCek->role != 'admin') {
            return response()->json(['message' => 'Your token is not admin'], $this->errorStatus);
        }

        $input = $request->all();
        $schedule = Schedule::create($input);

        return response()->json(['success' => $schedule], $this->successStatus);
    }

    public function updateSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'name' => 'required|unique:schedules,name,' . $request->id,
            'description' => 'required',
            'posted_at' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $userCek = Auth::user();

        if ($userCek->role != 'admin') {
            return response()->json(['message' => 'Your token is not admin'], $this->errorStatus);
        }

        $input = $request->all();
        $schedule = Schedule::find($request->id)->update($input);

        return response()->json(['success' => 'Successfully updated schedule'], $this->successStatus);
    }

    public function userScheduleDetail(Request $request)
    {
        $schedule = Schedule::find($request->schedule_id);

        return response()->json(['success' => $schedule], $this->successStatus);
    }

    public function userScheduleDelete(Request $request)
    {
        $user = Auth::user();

        if ($user->role != 'admin') {
            return response()->json(['message' => 'Your token is not admin'], $this->errorStatus);
        }

        $userScheduleDel = Schedule::findOrFail($request->schedule_id);
        $userScheduleDel->delete();

        return response()->json(['message' => 'Successfully deleted schedule'], $this->successStatus);
    }
}
