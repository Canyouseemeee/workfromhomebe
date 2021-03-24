<?php

namespace App\Http\Controllers;
use Carbon\Carbon;

use App\Models\CheckIn;
use App\Models\Task;
use App\Models\Solvework;
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

    public function getcheckin(Request $request)
    {
        $userid = $request->input('userid');
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
            ->where('users.id',$userid)
            ->orderBy('checkin_work.checkinid', 'DESC')
            ->get();

            // echo(Carbon::now());

        return response()->json($data);
    }

    public function gethistorycheckin(Request $request)
    {
        $userid = $request->input('userid');
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
            ->where('users.id',$userid)
            ->orderBy('checkin_work.checkinid', 'DESC')
            ->get();

            // echo(Carbon::now());

        return response()->json($data);
    }

    public function gethistorybetweencheckin(Request $request)
    {
        $userid = $request->input('userid');
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
            ->where('users.id',$userid)
            ->orderBy('checkin_work.checkinid', 'DESC')
            ->get();

            // echo(Carbon::now());

        return response()->json($data);
    }

    public function posttask(Request $request)
    {
        $userid = $request->input('userid');
        $subject = $request->input('subject');
        $description = $request->input('description');
        $assignment = $request->input('assignment');
        $duedate = $request->input('duedate');
        $departmentid = $request->input('departmentid');

        $task = new Task();
        $task->createtask = $userid;
        $task->subject = $subject;
        $task->description = $description;
        $task->statustask = 1;
        $task->departmentid = $departmentid;
        $task->assignment = $assignment;
        $task->assign_date = DateThai(Carbon::now());
        $task->due_date = $duedate;
        $task->close_date = null;
        $task->created_at = DateThai(Carbon::now());
        $task->updated_at = DateThai(Carbon::now());

        if ($request->hasFile('file')) {
            $filename = $request->file->getClientOriginalName();
            $file = time() . '.' . $filename;
            $task->file = $request->file->storeAs('files', $file, 'public');
            // dd($file);
        } else {
            $task->file = null;
        }

        $task->save();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function postsubmittask(Request $request)
    {
        $userid = $request->input('userid');
        $taskid = $request->input('taskid');

        $task = Task::find($taskid);
        $task->statustask = 3;
        $task->close_date = Carbon::today();
        $task->updated_at = DateThai(Carbon::now());

        if ($request->hasFile('file')) {
            $filename = $request->file->getClientOriginalName();
            $file = time() . '.' . $filename;
            $task->file = $request->file->storeAs('files', $file, 'public');
            // dd($file);
        } else {
            $task->file = null;
        }

        $task->update();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function updatetask(Request $request)
    {
        $taskid = $request->input('taskid');
        $userid = $request->input('userid');
        $subject = $request->input('subject');
        $description = $request->input('description');
        $assignment = $request->input('assignment');
        $duedate = $request->input('duedate');
        $departmentid = $request->input('departmentid');

        $task = Task::find($taskid);
        $task->createtask = $userid;
        $task->subject = $subject;
        $task->description = $description;
        // $task->statustask = 1;
        $task->departmentid = $departmentid;
        $task->assignment = $assignment;
        // $task->assign_date = DateThai(Carbon::now());
        $task->due_date = $duedate;
        // $task->created_at = DateThai(Carbon::now());
        $task->updated_at = DateThai(Carbon::now());

        if ($request->hasFile('file')) {
            $filename = $request->file->getClientOriginalName();
            $file = time() . '.' . $filename;
            $task->file = $request->file->storeAs('files', $file, 'public');
            // dd($file);
        } else {
            $task->file = null;
        }

        $task->update();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function gettask(Request $request)
    {
        $userid = $request->input('userid');

        $data = DB::table('task')
        ->select(
            'taskid',
            'createtask',
            'subject',
            'description',
            'statustask.statustaskid',
            'task.departmentid',
            'departments.dmname',
            'assignment',
            'users.name',
            'file',
            'assign_date',
            'due_date',
        )
        ->join('users', 'task.createtask', '=', 'users.id')
        ->join('statustask', 'task.statustask', '=', 'statustask.statustaskid')
        ->join('departments', 'task.departmentid', '=', 'departments.departmentid')
        ->where('users.id',$userid)
        ->orderBy('task.taskid', 'DESC')
        ->get();

        // echo($data->createtask);

        // echo(Carbon::now());

        return response()->json($data);
    }

    public function getassigntask(Request $request)
    {
        $userid = $request->input('userid');

        $data = DB::table('task')
        ->select(
            'taskid',
            'createtask',
            'subject',
            'description',
            'statustask.statustaskid',
            'task.departmentid',
            'departments.dmname',
            'assignment',
            'users.name',
            'file',
            'assign_date',
            'due_date',
        )
        ->join('users', 'task.assignment', '=', 'users.id')
        ->join('statustask', 'task.statustask', '=', 'statustask.statustaskid')
        ->join('departments', 'task.departmentid', '=', 'departments.departmentid')
        ->where('task.assignment',$userid)
        ->whereIn('task.statustask',array(1,2))
        ->orderBy('task.taskid', 'DESC')
        ->get();

        // echo($data->createtask);

        // echo(Carbon::now());

        return response()->json($data);
    }

    public function gethistoryassigntask(Request $request)
    {
        $userid = $request->input('userid');

        $data = DB::table('task')
        ->select(
            'taskid',
            'createtask',
            'subject',
            'description',
            'statustask.statustaskid',
            'task.departmentid',
            'departments.dmname',
            'assignment',
            'users.name',
            'file',
            'assign_date',
            'due_date',
        )
        ->join('users', 'task.assignment', '=', 'users.id')
        ->join('statustask', 'task.statustask', '=', 'statustask.statustaskid')
        ->join('departments', 'task.departmentid', '=', 'departments.departmentid')
        ->where('task.assignment',$userid)
        ->where('task.close_date', Carbon::now()->toDateString())
        ->where('task.statustask',3)
        ->orderBy('task.taskid', 'DESC')
        ->get();

        // echo($data->createtask);

        // echo(Carbon::now());

        return response()->json($data);
    }

    public function gethistorybetweenassigntask(Request $request)
    {
        $userid = $request->input('userid');
        $fromdate = $request->input('fromdate');
        $todate = $request->input('todate');

    
        $data = DB::table('task')
        ->select(
            'taskid',
            'createtask',
            'subject',
            'description',
            'statustask.statustaskid',
            'task.departmentid',
            'departments.dmname',
            'assignment',
            'users.name',
            'file',
            'assign_date',
            'due_date',
        )
        ->join('users', 'task.assignment', '=', 'users.id')
        ->join('statustask', 'task.statustask', '=', 'statustask.statustaskid')
        ->join('departments', 'task.departmentid', '=', 'departments.departmentid')
        ->where('task.assignment',$userid)
        ->whereBetween('task.close_date', [$fromdate, $todate])
        ->where('task.statustask',3)
        ->orderBy('task.taskid', 'DESC')
        ->get();

            // echo(Carbon::now());

        return response()->json($data);
    }

    public function poststatustask(Request $request)
    {
        $taskid = $request->input('taskid');

        $task = Task::find($taskid);
        $task->statustask = 2;
        $task->updated_at = DateThai(Carbon::now());


        $task->update();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function getdepartment()
    {
        $data = DB::table('departments')
            ->select(
                'departmentid',
                'dmname',
                
            )->get();

        // echo($data->createtask);

        // echo(Carbon::now());

        return response()->json($data);
    }

    public function getuser(Request $request)
    {
        $departmentid = $request->input('departmentid');
        $data = DB::table('users')
            ->select(
                'id',
                'name', 
            )
            ->join('departments', 'users.departmentid', '=', 'departments.departmentid')
            ->where('departments.departmentid',$departmentid)
            ->get();

            // echo(Carbon::now());

        return response()->json($data);
    }

    public function postretask(Request $request)
    {
        $taskid = $request->input('taskid');
        $userid = $request->input('userid');
        $subject = $request->input('subject');
        $assignment = $request->input('assignment');
        $duedate = $request->input('duedate');
        $departmentid = $request->input('departmentid');
        
        $solvework = new Solvework();
        $solvework->taskid = $taskid;
        $solvework->createsolvework = $userid;
        $solvework->subject = $subject;
        $solvework->statussolvework = 1;
        $solvework->departmentid = $departmentid;
        $solvework->assignment = $assignment;
        $solvework->assign_date = DateThai(Carbon::now());
        $solvework->due_date = $duedate;
        $solvework->close_date = null;
        $solvework->created_at = DateThai(Carbon::now());
        $solvework->updated_at = DateThai(Carbon::now());

        if ($request->hasFile('file')) {
            $filename = $request->file->getClientOriginalName();
            $file = time() . '.' . $filename;
            $solvework->file = $request->file->storeAs('files', $file, 'public');
            // dd($file);
        } else {
            $solvework->file = null;
        }

        $solvework->save();

        // $task = Task::find($taskid);
        // $task->statustask = 2;

        // $task->update();

        // echo($data->createtask);

        // echo(Carbon::now());

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function updatesolve(Request $request)
    {
        $solveworkid = $request->input('solveworkid');
        $subject = $request->input('subject');
        $duedate = $request->input('duedate');
        
        $solvework = Solvework::find($solveworkid);
        $solvework->subject = $subject;
        $solvework->due_date = $duedate;
        $solvework->updated_at = DateThai(Carbon::now());

        $solvework->update();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function countsolvework(Request $request){
        $taskid = $request->input('taskid');
        $comment = DB::table('solvework')
            ->select('*')
            ->join('task', 'task.taskid', '=', 'solvework.taskid')
            ->where('solvework.taskid',$taskid)
            ->count();
        return response()->json($comment);
    }

    public function getsolvework(Request $request)
    {
        $taskid = $request->input('taskid');

        $data = DB::table('solvework')
        ->select(
            'solveworkid',
            'solvework.taskid',
            'createsolvework',
            'solvework.subject',
            'statussolvework.statussolveworkid',
            'solvework.departmentid',
            'departments.dmname',
            'solvework.assignment',
            'users.name',
            'solvework.file',
            'solvework.assign_date',
            'solvework.due_date',
            'solvework.close_date',
        )
        ->join('task', 'task.taskid', '=', 'solvework.taskid')
        ->join('users', 'solvework.createsolvework', '=', 'users.id')
        ->join('statussolvework', 'solvework.statussolvework', '=', 'statussolvework.statussolveworkid')
        ->join('departments', 'solvework.departmentid', '=', 'departments.departmentid')
        ->where('solvework.taskid',$taskid)
        ->orderBy('solvework.taskid', 'DESC')
        ->get();

        // echo($data->createtask);

        // echo(Carbon::now());

        return response()->json($data);
    }

    public function postsubmitsolvework(Request $request)
    {
        $taskid = $request->input('taskid');
        $solveworkid = $request->input('solveworkid');

        $solvework = Solvework::find($solveworkid);
        $solvework->statussolvework = 2;
        $solvework->close_date = Carbon::today();
        $solvework->updated_at = DateThai(Carbon::now());

        if ($request->hasFile('file')) {
            $filename = $request->file->getClientOriginalName();
            $file = time() . '.' . $filename;
            $solvework->file = $request->file->storeAs('files', $file, 'public');
            // dd($file);
        } else {
            $solvework->file = null;
        }


        $solvework->update();

        // $task = Task::find($taskid);
        // $task->statussolvework = 3;
        // $task->updated_at = DateThai(Carbon::now());
        // $task->update();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function poststatussolve(Request $request)
    {
        $taskid = $request->input('taskid');


        $task = Task::find($taskid);
        $task->statustask = 3;
        $task->updated_at = DateThai(Carbon::now());
        $task->update();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function download(){
        $path=public_path('storage/files/1616421854.04_Fun_little_kid_game2.pdf');
        return response()->download($path);
    }
    
}
