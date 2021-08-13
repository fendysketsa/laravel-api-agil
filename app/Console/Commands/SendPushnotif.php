<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedule;
use App\Models\User;


class SendPushnotif extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = Auth::user();

        if ($user->role != 'admin') {
            return false;
        }

        $contentNotif['vibrate'] = 1;
        $contentNotif['sound'] = 1;
        $contentNotif['title'] = '';
        $contentNotif['body'] = '';

        $dataSchedule = Schedule::all();

        $dateCur = Carbon::parse(now())->format('Y-m-d H:i');

        $lanjut = 'FALSE';
        foreach ($dataSchedule as $index => $sch) {
            if (!empty($sch->posted_at)) {
                $date[$index] = $sch->posted_at;

                $satuJamSebelum[$index] = date('Y-m-d H:i', strtotime($date[$index] . ' - 60 minutes'));
                if ($dateCur == $satuJamSebelum[$index]) {
                    $lanjut = 'TRUE';

                    $contentNotif["title"] .= $sch->name;
                    $contentNotif["body"] .= $sch->description;
                }

                $setengahJamSebelum[$index] = date('Y-m-d H:i', strtotime($date[$index] . ' - 30 minutes'));
                if ($dateCur == $setengahJamSebelum[$index]) {
                    $lanjut = 'TRUE';

                    $contentNotif["title"] .= $sch->name;
                    $contentNotif["body"] .= $sch->description;
                }
            }
        }

        if ($lanjut == 'FALSE') {
            return false;
        }

        $url = 'https://fcm.googleapis.com/fcm/send';
        $FcmToken = User::where('role', 'team')->whereNotNull('fcm')->pluck('fcm')->all();

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
    }
}
