<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\{School, Admin, SchoolSetting};
use Illuminate\Support\Facades\DB;
$slug = 'qa-new-school-user-management';
if (($argv[1] ?? '') === 'cleanup') {
    School::where('slug', $slug)->delete();
    echo "Temporary school removed.\n";
    exit;
}
if (School::where('slug', $slug)->exists()) throw new RuntimeException('Fixture already exists.');
DB::transaction(function () use ($slug) {
    $school = School::create(['name'=>'QA New School', 'slug'=>$slug, 'status'=>'active', 'timezone'=>'Asia/Manila']);
    $school->schoolSetting()->create([
        'primary_color'=>'#667eea', 'secondary_color'=>'#764ba2', 'font_family'=>'Segoe UI',
    ]);
    Admin::create(['school_id'=>$school->id, 'name'=>'QA School Admin', 'email'=>'qa-new-school@example.invalid', 'password'=>'QA-Local-Only-123!', 'role'=>'school_admin', 'is_active'=>true, 'must_reset_password'=>false]);
    if ($school->courses()->exists()) throw new RuntimeException('New school must have zero courses.');
});
echo "Prepared isolated new school with zero courses.\n";
