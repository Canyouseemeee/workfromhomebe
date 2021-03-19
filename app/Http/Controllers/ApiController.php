<?php

namespace App\Http\Controllers;
use Carbon\Carbon;

use App\Models\CheckIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

function DateThai($strDate)
{
    $strYear = date("Y", strtotime($strDate));
    $strMonth = date("m", strtotime($strDate));
    $strDay = date("d", strtotime($strDate));
    $strHour = date("H", strtotime($strDate));
    $strMinute = date("i", strtotime($strDate));
    $strSeconds = date("s", strtotime($strDate));
    return "$strYear-$strMonth-$strDay $strHour:$strMinute:$strSeconds";
}

function DateThai2($strDate)
{
    $strYear = date("Y", strtotime($strDate));
    $strMonth = date("m", strtotime($strDate));
    $strDay = date("d", strtotime($strDate));
    $strHour = date("H", strtotime($strDate)) + 7;
    $strMinute = date("i", strtotime($strDate));
    $strSeconds = date("s", strtotime($strDate));
    if ($strHour < 10 && $strHour >= 0) {
        return "$strYear$strMonth$strDay 0$strHour$strMinute$strSeconds";
    } else {
        return "$strYear$strMonth$strDay$strHour$strMinute$strSeconds";
    }
}

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $input = $request->only('username', 'password');
        $username = $request->only('username');
        $userinfo = DB::table('users')
            ->select('*')
            ->where([['username', $username], ['active', 1]])
            ->get();

        $logintype = 0;
        foreach ($userinfo as $uinfo) {
            // echo $uinfo->name;
            // $isuser = 1;
            $userid = $uinfo->id;
            $logintype = $uinfo->logintype;
            $name = $uinfo->name;
            $usertypeid = $uinfo->usertypeid;
            $departmentid = $uinfo->departmentid;
            $latitude = $uinfo->latitude;
            $longitude = $uinfo->longitude;
            // $userprofile = array("id" => $uinfo->id, "logintype" => $logintype);
        }
        $department = DB::table('departments')
            ->select('*')
            ->where([['departmentid', $departmentid], ['status', 1]])
            ->get();

        foreach ($department as $dpm) {
            $dmname = $dpm->dmname;
        }

        if ($usertypeid == 1) {
            $usertype = "ADMIN";
        } elseif ($usertypeid == 2) {
            $usertype = "SUPERUSER";
        } elseif ($usertypeid == 3) {
            $usertype = "USER";
        }
        $token = openssl_random_pseudo_bytes(20);
        $token2 = bin2hex($token);
        $expires_at = DateThai2(now()->addHours(8));

        if ($logintype == 1) {
            if (Auth::loginUsingId($uinfo->id, TRUE)) {
                return response()->json([
                    'status' => 'Success',
                    'token' => $token2,
                    'usertype' => $usertype,
                    'logintype' => 'AD',
                    'userid' => $userid,
                    'input' => $username,
                    'name' => $name,
                    'expired_at' => $expires_at,
                    'department' => $dmname,
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ]);
            }
        } else if ($logintype == 0) {
            if ($token = Auth::attempt($input)) {
                return response()->json([
                    'status' => 'Success',
                    'token' => $token2,
                    'usertype' => $usertype,
                    'logintype' => 'DB',
                    'userid' => $userid,
                    'input' => $username,
                    'name' => $name,
                    'expired_at' => $expires_at,
                    'department' => $dmname,
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ]);
            }
        }
        if (!$token = Auth::attempt($input)) {
            return response()->json([
                'status' => 'Faild',
                'message' => 'Login Faild',
            ], 401);
        }
    }

    public function loginad(Request $request)
    {
        $input = $request->only('username');
        $userinfo = DB::table('users')
            ->select('*')
            ->where([['username', $input], ['active', 1]])
            ->get();

        $logintype = 0;
        foreach ($userinfo as $uinfo) {
            // echo $uinfo->name;
            $isuser = 1;
            $logintype = $uinfo->logintype;
            $userprofile = array("id" => $uinfo->id, "logintype" => $logintype);
        }
        $token = openssl_random_pseudo_bytes(20);
        $token2 = bin2hex($token);
        if (!Auth::loginUsingId($uinfo->id, TRUE)) {
            return response()->json([
                'status' => 'Faild',
                'message' => 'Login Faild',
            ], 401);
        }
        $expires_at = DateThai(now()->addHour(1));

        return response()->json([
            'status' => 'Success',
            'token' => $token2,
            // 'token' => $jwt_token,
            'input' => $input,
            'expires_at' => $expires_at
        ]);
    }

    public function checktoken(Request $request)
    {
        $_token = $request->input('token');

        return response()->json([
            'action' => 'checktoken',
            'status' => 'Success'
        ]);
    }

    public function postcheckin(Request $request)
    {
        $userid = $request->input('userid');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        $checkin = new CheckIn();
        $checkin->userid = $userid;
        $checkin->date_start = DateThai(Carbon::now());
        $checkin->date_end = null;
        $checkin->date_in = Carbon::today();
        $checkin->status = 1;
        $checkin->latitude = $latitude;
        $checkin->longitude = $longitude;
        $checkin->created_at = DateThai(Carbon::now());
        $checkin->updated_at = DateThai(Carbon::now());

        if ($request->hasFile('file')) {
            $filename = $request->file->getClientOriginalName();
            $file = time() . '.' . $filename;
            $checkin->file = $request->file->storeAs('files', $file, 'public');
            // dd($file);
        } else {
            $checkin->file = null;
        }

        $checkin->save();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function postcheckout(Request $request)
    {
        $checkinid = $request->input('checkinid');

        $checkin = CheckIn::find($checkinid);
        $checkin->date_end =  DateThai(Carbon::now());
        $checkin->status = 2;
        $checkin->updated_at = DateThai(Carbon::now());

        $checkin->update();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function getcheckin()
    {
        $data = DB::table('checkin_work')
            ->select(
                'checkinid',
                'users.name',
                'date_start',
                'date_end',
                'status',
                'file',
                'checkin_work.latitude',
                'checkin_work.longitude',
            )
            ->join('users', 'checkin_work.userid', '=', 'users.id')
            ->join('statuscheckin', 'checkin_work.status', '=', 'statuscheckin.statusid')
            ->where('checkin_work.date_in', Carbon::now()->toDateString())
            ->orderBy('checkin_work.checkinid', 'DESC')
            ->get();

            // echo(Carbon::now());

        return response()->json($data);
    }

    public function gethistorycheckin()
    {
        $data = DB::table('checkin_work')
            ->select(
                'checkinid',
                'users.name',
                'date_start',
                'date_end',
                'status',
                'file',
                'checkin_work.latitude',
                'checkin_work.longitude',
            )
            ->join('users', 'checkin_work.userid', '=', 'users.id')
            ->join('statuscheckin', 'checkin_work.status', '=', 'statuscheckin.statusid')
            ->where('checkin_work.date_in', Carbon::now()->toDateString())
            ->orderBy('checkin_work.checkinid', 'DESC')
            ->get();

            // echo(Carbon::now());

        return response()->json($data);
    }

    public function gethistorybetweencheckin(Request $request)
    {

        $fromdate = $request->input('fromdate');
        $todate = $request->input('todate');

        $data = DB::table('checkin_work')
            ->select(
                'checkinid',
                'users.name',
                'date_start',
                'date_end',
                'status',
                'file',
                'checkin_work.latitude',
                'checkin_work.longitude',
            )
            ->join('users', 'checkin_work.userid', '=', 'users.id')
            ->join('statuscheckin', 'checkin_work.status', '=', 'statuscheckin.statusid')
            ->whereBetween('checkin_work.date_in', [$fromdate, $todate])
            ->orderBy('checkin_work.checkinid', 'DESC')
            ->get();

            // echo(Carbon::now());

        return response()->json($data);
    }
}
