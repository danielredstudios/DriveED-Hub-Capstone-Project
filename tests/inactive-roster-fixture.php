<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\{School, Admin, Student, Instructor};
use App\Http\Controllers\{AdminController, SystemAdminController};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
$slug = 'qa-inactive-roster';
if (($argv[1] ?? '') === 'cleanup') {
    School::whereIn('slug', [$slug, $slug.'-other'])->delete();
    echo "PASS: Temporary status-test schools removed.\n";
    exit;
}
if (($argv[1] ?? '') === 'split-schools') {
    $school = School::where('slug', $slug)->firstOrFail();
    $other = School::where('slug', $slug.'-other')->firstOrFail();
    Instructor::where('school_id', $other->id)->where('email', 'other-inactive@example.invalid')->delete();
    Instructor::where('school_id', $school->id)->where('email', 'qa-instructor-3@example.invalid')->update(['school_id'=>$other->id]);
    $other->schoolSetting()->firstOrCreate([], ['primary_color'=>'#2563eb', 'secondary_color'=>'#f59e0b']);
    Admin::firstOrCreate(['school_id'=>$other->id, 'email'=>'qa-other-admin@example.invalid'], ['name'=>'Other School Admin', 'password'=>'QA-Local-Only-123!', 'role'=>'school_admin', 'is_active'=>true]);
    foreach ([[$school, 2, 2], [$other, 0, 1]] as [$target, $students, $instructors]) {
        Auth::guard('admin')->setUser(Admin::where('school_id', $target->id)->firstOrFail());
        $request = Request::create('/'.$target->slug.'/admin/user-management', 'GET', ['status'=>'inactive', 'filtered'=>1]);
        app()->instance('request', $request);
        $data = app(AdminController::class)->userManagement($request, $target)->getData();
        if ($data['inactiveStudents']!==$students || $data['inactiveInstructors']!==$instructors || $data['users']->total()!==$students+$instructors) throw new RuntimeException('School-scoped count mismatch.');
        echo "PASS: {$target->slug}: {$students} inactive students, {$instructors} inactive instructors; roster matches card.\n";
    }
    exit;
}
if (School::whereIn('slug', [$slug, $slug.'-other'])->exists()) throw new RuntimeException('Clean previous fixture first.');
DB::transaction(function () use ($slug) {
    $school = School::create(['name'=>'Inactive Users Verification', 'slug'=>$slug, 'status'=>'active']);
    $school->schoolSetting()->create(['primary_color'=>'#2563eb', 'secondary_color'=>'#f59e0b']);
    $admin = Admin::create(['school_id'=>$school->id, 'name'=>'QA Admin', 'email'=>'qa-inactive-admin@example.invalid', 'password'=>'QA-Local-Only-123!', 'role'=>'school_admin', 'is_active'=>true]);
    $system = app(SystemAdminController::class);
    foreach ([Student::class=>2, Instructor::class=>3] as $model=>$count) {
        for ($i=1; $i<=$count; $i++) {
            $type = $model === Student::class ? 'student' : 'instructor';
            $user = $model::create(array_merge(['school_id'=>$school->id, 'name'=>ucfirst($type).' '.$i, 'email'=>"qa-{$type}-{$i}@example.invalid", 'password'=>'QA-Local-Only-123!', 'status'=>'active'], $type==='student' ? ['role'=>'student'] : []));
            if ($type === 'instructor' && $i===3) {
                $system->toggleUserStatus($type, $user->id);
                if ($user->fresh()->status !== 'inactive') throw new RuntimeException('System-admin deactivation failed.');
            } else {
                $system->archiveUser($type, $user->id);
                if ($user->fresh()->status !== 'archived') throw new RuntimeException('System-admin archive failed.');
            }
        }
        $model::create(array_merge(['school_id'=>$school->id, 'name'=>'Active '.($model===Student::class?'Student':'Instructor'), 'email'=>'active-'.class_basename($model).'@example.invalid', 'password'=>'QA-Local-Only-123!', 'status'=>'active'], $model===Student::class ? ['role'=>'student'] : []));
    }
    $other = School::create(['name'=>'Other School Control', 'slug'=>$slug.'-other', 'status'=>'active']);
    Instructor::create(['school_id'=>$other->id, 'name'=>'Other School Inactive Instructor', 'email'=>'other-inactive@example.invalid', 'password'=>'QA-Local-Only-123!', 'status'=>'inactive']);
    Auth::guard('admin')->setUser($admin);
    $cases = [
        ['status'=>'inactive', 'count'=>5],
        ['status'=>'inactive', 'role'=>'student', 'count'=>2],
        ['status'=>'inactive', 'role'=>'instructor', 'count'=>3],
        ['status'=>'active', 'count'=>2],
        ['filtered'=>1, 'count'=>7],
        ['status'=>'inactive', 'search'=>'Instructor 3', 'count'=>1],
    ];
    foreach ($cases as $case) {
        $expected = $case['count']; unset($case['count']);
        $request = Request::create('/'.$slug.'/admin/user-management', 'GET', $case);
        app()->instance('request', $request);
        $view = app(AdminController::class)->userManagement($request, $school);
        $data = $view->getData();
        if ($data['inactiveStudents']!==2 || $data['inactiveInstructors']!==3 || $data['users']->total()!==$expected) throw new RuntimeException('Roster/count mismatch: '.json_encode($case));
        echo 'PASS: '.json_encode($case)." shows {$expected} matching users; card shows 2 students and 3 instructors.\n";
    }
});
echo "PASS: Fixture created using real system-admin archive/deactivate handlers; other-school users excluded.\n";
