<?php

namespace App\Http\Controllers;

use App\Imports\AttendanceImport;
use App\Models\AttendanceEmployee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\IpRestrict;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceEmployeeController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage attendance')) {

            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('Select Department', '');

            if (\Auth::user()->type != 'client' && \Auth::user()->type != 'company') {

                $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

                $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp);

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year = date('Y', strtotime($request->month));

                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {
                    $month = date('m');
                    $year = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                }
                $attendanceEmployee = $attendanceEmployee->get();

            } else {

                $employee = Employee::select('id')->where('created_by', \Auth::user()->creatorId());
                if (!empty($request->branch)) {
                    $employee->where('branch_id', $request->branch);
                }

                if (!empty($request->department)) {
                    $employee->where('department_id', $request->department);
                }

                $employee = $employee->get()->pluck('id');

                $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employee);
                if ($request->type == 'monthly' && !empty($request->month)) {

                    $month = date('m', strtotime($request->month));
                    $year = date('Y', strtotime($request->month));
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));


                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {

                    $month = date('m');
                    $year = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));


                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                }

                $attendanceEmployee = $attendanceEmployee->get();
            }

            return view('attendance.index', compact('attendanceEmployee', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create attendance')) {
            $employees = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '=', "employee")->get()->pluck('name', 'id');

            return view('attendance.create', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create attendance')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'date' => 'required',
                    'clock_in' => 'required',
                    'clock_out' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $startTime = Utility::getValByName('company_start_time');
            $endTime = Utility::getValByName('company_end_time');
            $attendance = AttendanceEmployee::where('employee_id', '=', $request->employee_id)->where('date', '=', $request->date)->where('clock_out', '=', '00:00:00')->get()->toArray();
            if ($attendance) {
                return redirect()->route('attendanceemployee.index')->with('error', __('Employee Attendance Already Created.'));
            } else {
                $date = date("Y-m-d");

                $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . $startTime);

                $hours = floor($totalLateSeconds / 3600);
                $mins = floor($totalLateSeconds / 60 % 60);
                $secs = floor($totalLateSeconds % 60);

                $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                //early Leaving
                $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($request->clock_out);
                $hours = floor($totalEarlyLeavingSeconds / 3600);
                $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                if (strtotime($request->clock_out) > strtotime($date . $endTime)) {
                    //Overtime
                    $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . $endTime);
                    $hours = floor($totalOvertimeSeconds / 3600);
                    $mins = floor($totalOvertimeSeconds / 60 % 60);
                    $secs = floor($totalOvertimeSeconds % 60);
                    $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                } else {
                    $overtime = '00:00:00';
                }

                $employeeAttendance = new AttendanceEmployee();
                $employeeAttendance->employee_id = $request->employee_id;
                $employeeAttendance->date = $request->date;
                $employeeAttendance->status = 'Present';
                $employeeAttendance->clock_in = $request->clock_in . ':00';
                $employeeAttendance->clock_out = $request->clock_out . ':00';
                $employeeAttendance->late = $late;
                $employeeAttendance->early_leaving = $earlyLeaving;
                $employeeAttendance->overtime = $overtime;
                $employeeAttendance->total_rest = '00:00:00';
                $employeeAttendance->created_by = \Auth::user()->creatorId();
                $employeeAttendance->save();

                return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully created.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show()
    {
        return redirect()->route('attendanceemployee.index');
    }

    public function edit($id)
    {
        if (\Auth::user()->can('edit attendance')) {
            $attendanceEmployee = AttendanceEmployee::where('id', $id)->first();
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('attendance.edit', compact('attendanceEmployee', 'employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {

        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'HR') {
            $employeeId = AttendanceEmployee::where('employee_id', $request->employee_id)->first();
            $check = AttendanceEmployee::where('id', $id)->where('employee_id', '=', $request->employee_id)->where('date', $request->date)->first();

            $startTime = Utility::getValByName('company_start_time');
            $endTime = Utility::getValByName('company_end_time');

            $clockIn = $request->clock_in;
            $clockOut = $request->clock_out;

            if ($clockIn) {
                $status = "present";
            } else {
                $status = "leave";
            }

            $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);

            $hours = floor($totalLateSeconds / 3600);
            $mins = floor($totalLateSeconds / 60 % 60);
            $secs = floor($totalLateSeconds % 60);
            $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
            $hours = floor($totalEarlyLeavingSeconds / 3600);
            $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            if (strtotime($clockOut) > strtotime($endTime)) {
                //Overtime
                $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
                $hours = floor($totalOvertimeSeconds / 3600);
                $mins = floor($totalOvertimeSeconds / 60 % 60);
                $secs = floor($totalOvertimeSeconds % 60);
                $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }
            if ($check->date == date('Y-m-d')) {
                $check->update([
                    'late' => $late,
                    'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                    'overtime' => $overtime,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                ]);

                return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
            } else {
                return redirect()->route('attendanceemployee.index')->with('error', __('you can only update current day attendance.'));
            }
        }

        $employeeId = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
        $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();
        //        if(!empty($todayAttendance) && $todayAttendance->clock_out == '00:00:00')
        //        if($todayAttendance->clock_out == '00:00:00')
        //        {

        $startTime = Utility::getValByName('company_start_time');
        $endTime = Utility::getValByName('company_end_time');

        if (Auth::user()->type == 'Employee') {

            $date = date("Y-m-d");
            $time = date("H:i:s");
            //early Leaving

            $companyEndTimestamp = strtotime($date . ' ' . $endTime);
            $clockOutTimestamp = strtotime($date . ' ' . $time);

            if($clockOutTimestamp < $companyEndTimestamp){
                $totalEarlyLeavingSeconds = $companyEndTimestamp - $clockOutTimestamp;
                $hours = floor($totalEarlyLeavingSeconds / 3600);
                $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $earlyLeaving = '00:00:00';
            }

            if (time() > strtotime($date . $endTime)) {
                //Overtime
                $totalOvertimeSeconds = time() - strtotime($date . $endTime);
                $hours = floor($totalOvertimeSeconds / 3600);
                $mins = floor($totalOvertimeSeconds / 60 % 60);
                $secs = floor($totalOvertimeSeconds % 60);
                $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            $attendanceEmployee['clock_out'] = $time;
            $attendanceEmployee['early_leaving'] = $earlyLeaving;
            $attendanceEmployee['overtime'] = $overtime;

            if (!empty($request->date)) {
                $attendanceEmployee['date'] = $request->date;
            }
            AttendanceEmployee::where('id', $id)->update($attendanceEmployee);

            return redirect()->route('hrm.dashboard')->with('success', __('Employee successfully clock Out.'));
        } else {
            $date = date("Y-m-d");
            $clockout_time = date("H:i:s");
            //late
            $totalLateSeconds = strtotime($clockout_time) - strtotime($date . $startTime);

            $hours = abs(floor($totalLateSeconds / 3600));
            $mins = abs(floor($totalLateSeconds / 60 % 60));
            $secs = abs(floor($totalLateSeconds % 60));

            $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            //early Leaving
            $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($clockout_time);
            $hours = floor($totalEarlyLeavingSeconds / 3600);
            $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            if (strtotime($clockout_time) > strtotime($date . $endTime)) {
                //Overtime
                $totalOvertimeSeconds = strtotime($clockout_time) - strtotime($date . $endTime);
                $hours = floor($totalOvertimeSeconds / 3600);
                $mins = floor($totalOvertimeSeconds / 60 % 60);
                $secs = floor($totalOvertimeSeconds % 60);
                $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            $attendanceEmployee = AttendanceEmployee::find($id);
            // $attendanceEmployee->employee_id   = $employeeId;
            // $attendanceEmployee->date          = $request->date;
            // $attendanceEmployee->clock_in      = $request->clock_in;
            $attendanceEmployee->clock_out = $clockout_time;
            $attendanceEmployee->late = $late;
            $attendanceEmployee->early_leaving = $earlyLeaving;
            $attendanceEmployee->overtime = $overtime;
            $attendanceEmployee->total_rest = '00:00:00';

            $attendanceEmployee->save();

            return redirect()->back()->with('success', __('Employee attendance successfully updated.'));
        }
        //        }
        //        else
        //        {
        //            return redirect()->back()->with('error', __('Employee are not allow multiple time clock in & clock for every day.'));
        //        }
    }

    public function destroy($id)
    {
        if (\Auth::user()->can('delete attendance')) {
            $attendance = AttendanceEmployee::where('id', $id)->first();

            $attendance->delete();

            return redirect()->route('attendanceemployee.index')->with('success', __('Attendance successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function attendance(Request $request)
    {
        $settings = Utility::settings();

        if ($settings['ip_restrict'] == 'on') {
            $userIp = request()->ip();
            $ip = IpRestrict::where('created_by', \Auth::user()->creatorId())->whereIn('ip', [$userIp])->first();
            if (empty($ip)) {
                return redirect()->back()->with('error', __('This ip is not allowed to clock in & clock out.'));
            }
        }
        $employeeId = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

        $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->orderBy('id', 'desc')->first();
        //        if(empty($todayAttendance))
        //        {

        $startTime = Utility::getValByName('company_start_time');
        $endTime = Utility::getValByName('company_end_time');

        // Find the last clocked out entry for the employee
        $lastClockOutEntry = AttendanceEmployee::orderBy('id', 'desc')
            ->where('employee_id', '=', $employeeId)
            ->where('clock_out', '!=', '00:00:00')
            ->where('date', '=', date('Y-m-d'))
            ->first();

        $date = date("Y-m-d");
        $time = date("H:i:s");

        if($lastClockOutEntry != null) {
            $lastClockOutTime = $lastClockOutEntry->clock_out;
            $actualClockInTime = $date . ' ' . $time;

            $totalLateSeconds = strtotime($actualClockInTime) - strtotime($date . ' ' . $lastClockOutTime);

            // Ensure late time is non-negative
            $totalLateSeconds = max($totalLateSeconds, 0);

            $hours = abs(floor($totalLateSeconds / 3600));
            $mins = abs(floor($totalLateSeconds / 60 % 60));
            $secs = abs(floor($totalLateSeconds % 60));
            $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        } else {
            $expectedStartTime = $date . ' ' . $startTime;
            $actualClockInTime = $date . ' ' . $time;

            $totalLateSeconds = strtotime($actualClockInTime) - strtotime($expectedStartTime);

            $totalLateSeconds = max($totalLateSeconds, 0);

            $hours = abs(floor($totalLateSeconds / 3600));
            $mins = abs(floor($totalLateSeconds / 60 % 60));
            $secs = abs(floor($totalLateSeconds % 60));
            $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        }

        $checkDb = AttendanceEmployee::where('employee_id', '=', \Auth::user()->id)->get()->toArray();

        if (empty($checkDb)) {
            $employeeAttendance = new AttendanceEmployee();
            $employeeAttendance->employee_id = $employeeId;
            $employeeAttendance->date = $date;
            $employeeAttendance->status = 'Present';
            $employeeAttendance->clock_in = $time;
            $employeeAttendance->clock_out = '00:00:00';
            $employeeAttendance->late = $late;
            $employeeAttendance->early_leaving = '00:00:00';
            $employeeAttendance->overtime = '00:00:00';
            $employeeAttendance->total_rest = '00:00:00';
            $employeeAttendance->created_by = \Auth::user()->id;

            $employeeAttendance->save();

            return redirect()->back()->with('success', __('Employee Successfully Clock In.'));
        }
        foreach ($checkDb as $check) {

            $employeeAttendance = new AttendanceEmployee();
            $employeeAttendance->employee_id = $employeeId;
            $employeeAttendance->date = $date;
            $employeeAttendance->status = 'Present';
            $employeeAttendance->clock_in = $time;
            $employeeAttendance->clock_out = '00:00:00';
            $employeeAttendance->late = $late;
            $employeeAttendance->early_leaving = '00:00:00';
            $employeeAttendance->overtime = '00:00:00';
            $employeeAttendance->total_rest = '00:00:00';
            $employeeAttendance->created_by = \Auth::user()->id;

            $employeeAttendance->save();

            return redirect()->back()->with('success', __('Employee Successfully Clock In.'));

        }
        //        }
        //        else
        //        {
        //            return redirect()->back()->with('error', __('Employee are not allow multiple time clock in & clock for every day.'));
        //        }
    }

    public function bulkAttendance(Request $request)
    {
        if (\Auth::user()->can('create attendance')) {

            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('Select Department', '');

            $employees = [];
            if (!empty($request->branch) && !empty($request->department)) {
                $employees = Employee::where('created_by', \Auth::user()->creatorId())->where('branch_id', $request->branch)->where('department_id', $request->department)->get();

            } else {
                $employees = Employee::where('created_by', \Auth::user()->creatorId())->where('branch_id', 1)->where('department_id', 1)->get();
            }

            return view('attendance.bulk', compact('employees', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bulkAttendanceData(Request $request)
    {

        if (\Auth::user()->can('create attendance')) {
            if (!empty($request->branch) && !empty($request->department)) {
                $startTime = Utility::getValByName('company_start_time');
                $endTime = Utility::getValByName('company_end_time');
                $date = $request->date;

                $employees = $request->employee_id;
                $atte = [];

                if (!empty($employees)) {
                    foreach ($employees as $employee) {
                        $present = 'present-' . $employee;
                        $in = 'in-' . $employee;
                        $out = 'out-' . $employee;
                        $atte[] = $present;
                        if ($request->$present == 'on') {

                            $in = date("H:i:s", strtotime($request->$in));
                            $out = date("H:i:s", strtotime($request->$out));

                            $totalLateSeconds = strtotime($in) - strtotime($startTime);

                            $hours = floor($totalLateSeconds / 3600);
                            $mins = floor($totalLateSeconds / 60 % 60);
                            $secs = floor($totalLateSeconds % 60);
                            $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                            //early Leaving
                            $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($out);
                            $hours = floor($totalEarlyLeavingSeconds / 3600);
                            $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
                            $secs = floor($totalEarlyLeavingSeconds % 60);
                            $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                            if (strtotime($out) > strtotime($endTime)) {
                                //Overtime
                                $totalOvertimeSeconds = strtotime($out) - strtotime($endTime);
                                $hours = floor($totalOvertimeSeconds / 3600);
                                $mins = floor($totalOvertimeSeconds / 60 % 60);
                                $secs = floor($totalOvertimeSeconds % 60);
                                $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                            } else {
                                $overtime = '00:00:00';
                            }
                            $attendance = AttendanceEmployee::where('employee_id', '=', $employee)->where('date', '=', $request->date)->first();

                            if (!empty($attendance)) {
                                $employeeAttendance = $attendance;
                            } else {
                                $employeeAttendance = new AttendanceEmployee();
                                $employeeAttendance->employee_id = $employee;
                                $employeeAttendance->created_by = \Auth::user()->creatorId();
                            }
                            $employeeAttendance->date = $request->date;
                            $employeeAttendance->status = 'Present';
                            $employeeAttendance->clock_in = $in;
                            $employeeAttendance->clock_out = $out;
                            $employeeAttendance->late = $late;
                            $employeeAttendance->early_leaving = ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00';
                            $employeeAttendance->overtime = $overtime;
                            $employeeAttendance->total_rest = '00:00:00';
                            $employeeAttendance->save();

                        } else {
                            $attendance = AttendanceEmployee::where('employee_id', '=', $employee)->where('date', '=', $request->date)->first();

                            if (!empty($attendance)) {
                                $employeeAttendance = $attendance;
                            } else {
                                $employeeAttendance = new AttendanceEmployee();
                                $employeeAttendance->employee_id = $employee;
                                $employeeAttendance->created_by = \Auth::user()->creatorId();
                            }

                            $employeeAttendance->status = 'Leave';
                            $employeeAttendance->date = $request->date;
                            $employeeAttendance->clock_in = '00:00:00';
                            $employeeAttendance->clock_out = '00:00:00';
                            $employeeAttendance->late = '00:00:00';
                            $employeeAttendance->early_leaving = '00:00:00';
                            $employeeAttendance->overtime = '00:00:00';
                            $employeeAttendance->total_rest = '00:00:00';
                            $employeeAttendance->save();
                        }
                    }
                } else {
                    return redirect()->back()->with('error', __('Employee not found.'));
                }

                return redirect()->back()->with('success', __('Employee attendance successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Branch & department field required.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    //for attendance employee report

    public function importFile()
    {
        return view('attendance.import');
    }

    public function attendanceImportdata(Request $request)
    {
        session_start();
        $html = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>';
        $flag = 0;
        $html .= '<table class="table table-bordered"><tr>';
        try {
            $request = $request->data;
            $file_data = $_SESSION['file_data'];

            unset($_SESSION['file_data']);
        } catch (\Throwable $th) {
            $html = '<h3 class="text-danger text-center">Something went wrong, Please try again</h3></br>';
            return response()->json([
                'html' => true,
                'response' => $html,
            ]);
        }
        $user = Auth::user();

        $startTime = Utility::getValByName('company_start_time');
        $endTime = Utility::getValByName('company_end_time');

        foreach ($file_data as $key => $row) {
            $employeeData = Employee::Where('email', 'like', $row[$request['employee_email']])->where('created_by', \Auth::user()->creatorId())->first();

            if (!empty($employeeData)) {
                try {

                    $employeeId = $employeeData->id;

                    $clockIn = $row[$request['clock_in']];
                    $clockOut = $row[$request['clock_out']];

                    if ($clockIn) {
                        $status = "present";
                    } else {
                        $status = "leave";
                    }

                    $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);

                    $hours = floor($totalLateSeconds / 3600);
                    $mins = floor($totalLateSeconds / 60 % 60);
                    $secs = floor($totalLateSeconds % 60);
                    $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                    $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
                    $hours = floor($totalEarlyLeavingSeconds / 3600);
                    $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
                    $secs = floor($totalEarlyLeavingSeconds % 60);
                    $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                    if (strtotime($clockOut) > strtotime($endTime)) {
                        //Overtime
                        $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
                        $hours = floor($totalOvertimeSeconds / 3600);
                        $mins = floor($totalOvertimeSeconds / 60 % 60);
                        $secs = floor($totalOvertimeSeconds % 60);
                        $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    } else {
                        $overtime = '00:00:00';
                    }

                    $check = AttendanceEmployee::where('employee_id', $employeeId)->where('date', $row[$request['date']])->first();
                    if ($check) {
                        $check->update([
                            'late' => $late,
                            'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                            'overtime' => $overtime,
                            'clock_in' => $row[$request['clock_in']],
                            'clock_out' => $row[$request['clock_out']],
                        ]);
                    } else {
                        $time_sheet = AttendanceEmployee::create([
                            'employee_id' => $employeeId,
                            'date' => $row[$request['date']],
                            'status' => $status,
                            'late' => $late,
                            'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                            'overtime' => $overtime,
                            'clock_in' => $row[$request['clock_in']],
                            'clock_out' => $row[$request['clock_out']],
                            'created_by' => \Auth::user()->id,
                        ]);
                    }

                } catch (\Exception $e) {
                    $flag = 1;
                    $html .= '<tr>';

                    $html .= '<td>' . (isset($row[$request['employee_email']]) ? $row[$request['employee_email']] : '-') . '</td>';
                    $html .= '<td>' . (isset($row[$request['date']]) ? $row[$request['date']] : '-') . '</td>';
                    $html .= '<td>' . (isset($row[$request['clock_in']]) ? $row[$request['clock_in']] : '-') . '</td>';
                    $html .= '<td>' . (isset($row[$request['clock_out']]) ? $row[$request['clock_out']] : '-') . '</td>';

                    $html .= '</tr>';
                }
            } else {
                $flag = 1;
                $html .= '<tr>';

                $html .= '<td>' . (isset($row[$request['employee_email']]) ? $row[$request['employee_email']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['date']]) ? $row[$request['date']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['clock_in']]) ? $row[$request['clock_in']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['clock_out']]) ? $row[$request['clock_out']] : '-') . '</td>';

                $html .= '</tr>';
            }
        }

        $html .= '
                        </table>
                        <br />
                        ';
        if ($flag == 1) {

            return response()->json([
                'html' => true,
                'response' => $html,
            ]);
        } else {
            return response()->json([
                'html' => false,
                'response' => 'Data Imported Successfully',
            ]);
        }

    }
}
