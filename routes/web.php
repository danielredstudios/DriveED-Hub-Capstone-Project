<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminTimeSlotController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\InstructorTimeSlotController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\EnrollmentRequestController;
use App\Http\Controllers\SystemAdminController;
use App\Http\Controllers\SessionCompletionController;
use App\Http\Controllers\TheoreticalCompletionController;
use App\Http\Controllers\CourseModuleController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ModuleLessonController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PhaseProgressionController;
use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\AssessmentController;
use App\Models\School;

Route::get('/', function () {
    // Eager load schoolSetting to prevent N+1 queries
    $schools = \App\Models\School::with('schoolSetting')->orderBy('name')->get();
    return view('welcome', compact('schools'));
})->name('welcome');

// ========================================
// TEST ROUTES - Only available in local/dev
// ========================================
if (app()->environment('local', 'development')) {
    Route::prefix('test')->name('test.')->group(function () {
        Route::get('/course-form', function () {
                return view('test-components.course-form-enhanced');
            }
            )->name('course-form');

            // Test credentials page (dev only)
            Route::get('/credentials/{school:slug}', function (School $school) {
                return view('test-credentials', compact('school'));
            }
            )->name('credentials');
        });
}
// ========================================

// System Admin Routes (Global - Not School Specific)
Route::prefix('system-admin')->name('system-admin.')->group(function () {
    // Login routes (no auth required)
    Route::get('/login', [SystemAdminController::class , 'showLogin'])->name('login');
    Route::post('/login', [SystemAdminController::class , 'login'])->name('login.submit')->middleware('throttle:5,1');

    // Password reset routes (no auth required)
    Route::get('/forgot-password', [PasswordResetController::class , 'showSystemAdminForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class , 'sendResetLink'])->name('password.email')->middleware('throttle:3,1');
    Route::get('/reset-password/{token}', [PasswordResetController::class , 'showSystemAdminResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class , 'reset'])->name('password.update')->middleware('throttle:5,1');

    // Protected routes (system admin only)
    Route::middleware(['system.admin', 'nocache'])->group(function () {
            Route::get('/', [SystemAdminController::class , 'dashboard'])->name('dashboard');
            Route::get('/dashboard', [SystemAdminController::class , 'dashboard']);

            // Schools management
            Route::get('/schools', [SystemAdminController::class , 'schools'])->name('schools');
            Route::post('/schools', [SystemAdminController::class , 'storeSchool'])->name('schools.store');
            Route::patch('/schools/{school}/toggle-status', [SystemAdminController::class , 'toggleSchoolStatus'])->name('schools.toggle-status');
            Route::delete('/schools/{school}', [SystemAdminController::class , 'deleteSchool'])->name('schools.delete');

            // School Admins management
            Route::get('/admins', [SystemAdminController::class , 'admins'])->name('admins');
            Route::post('/admins', [SystemAdminController::class , 'storeAdmin'])->name('admins.store');
            Route::patch('/admins/{admin}/toggle-status', [SystemAdminController::class , 'toggleAdminStatus'])->name('admins.toggle-status');
            Route::delete('/admins/{admin}', [SystemAdminController::class , 'deleteAdmin'])->name('admins.delete');

            // Users management
            Route::get('/users', [SystemAdminController::class , 'users'])->name('users');
            Route::patch('/users/{type}/{id}/toggle-status', [SystemAdminController::class , 'toggleUserStatus'])->name('users.toggle-status')->whereIn('type', ['student', 'instructor']);
            Route::delete('/users/{type}/{id}', [SystemAdminController::class , 'deleteUser'])->name('users.delete')->where('type', 'student|instructor');

            // Logs
            Route::get('/logs', [SystemAdminController::class , 'logs'])->name('logs');
            Route::get('/logs/{log}', [SystemAdminController::class , 'showLog'])->name('logs.show');
            Route::post('/logs/{log}/resolve', [SystemAdminController::class , 'resolveLog'])->name('logs.resolve');
            Route::post('/logs/cleanup', [SystemAdminController::class , 'cleanupLogs'])->name('logs.cleanup');

            Route::post('/logout', [SystemAdminController::class , 'logout'])->name('logout');
        }
        );
    });

Route::prefix('{school:slug}')
    ->as('schools.')
    ->middleware(['school.context'])
    ->scopeBindings()
    ->group(function (): void {
        Route::controller(AuthController::class)->group(function (): void {
            Route::get('/', 'showLogin')->name('login');
            Route::get('/login', 'showLogin');
            Route::post('/login', 'login')->name('login.submit')->middleware(app()->environment('production') ? 'throttle:5,1' : 'throttle:60,1');
            Route::post('/logout', 'logout')->name('logout');

            // Force Password Reset (for first-time admins)
            Route::get('/force-password-reset', 'showForceResetForm')->name('password.force-reset');
            Route::post('/force-password-reset', 'handleForceReset')->name('password.force-reset.update');
        }
        );

        // Onboarding routes for invited users
        Route::controller(\App\Http\Controllers\Auth\OnboardingController::class)->group(function (): void {
            Route::get('/onboard/{token}', 'show')->name('onboarding.show');
            Route::post('/onboard/{token}', 'submit')->name('onboarding.submit');
        });

        // Password reset routes
        Route::get('/forgot-password', [PasswordResetController::class , 'showForgotForm'])->name('password.request');
        Route::post('/forgot-password', [PasswordResetController::class , 'sendResetLink'])->name('password.email')->middleware('throttle:3,1');
        Route::get('/reset-password/{token}', [PasswordResetController::class , 'showResetForm'])->name('password.reset');
        Route::post('/reset-password', [PasswordResetController::class , 'reset'])->name('password.update')->middleware('throttle:5,1');

        // Public guest registration (Main registration entry point)
        Route::get('/register', [GuestController::class , 'showRegistrationForm'])->name('registration.form');
        Route::post('/register', [GuestController::class , 'register'])->name('registration.submit');

        // Email verification routes
        Route::get('/verify-email', [GuestController::class , 'showVerificationForm'])->name('verification.show');
        Route::post('/verify-email', [GuestController::class , 'verifyEmail'])->name('verification.verify');
        Route::post('/resend-verification', [GuestController::class , 'resendVerificationCode'])->name('verification.resend');

        // Notification routes (accessible to all authenticated users)
        Route::prefix('notifications')->name('notifications.')->group(function (): void {
            Route::get('/', [NotificationController::class , 'index'])->name('index');
            Route::post('/{notification}/read', [NotificationController::class , 'markAsRead'])->name('markAsRead');
            Route::post('/mark-all-read', [NotificationController::class , 'markAllAsRead'])->name('markAllAsRead');
        }
        );

        // Guest-authenticated routes (must have guest role)
        Route::prefix('guest')->name('guest.')->group(function (): void {
            Route::middleware(['auth:student', 'guest.role', 'nocache'])->group(function (): void {
                    Route::get('/dashboard', [GuestController::class , 'dashboard'])->name('dashboard');
                    Route::get('/courses', [GuestController::class , 'courses'])->name('courses');
                    Route::post('/upload-license', [GuestController::class , 'uploadLicense'])->name('uploadLicense');
                    Route::get('/enrollment-requests', [GuestController::class , 'enrollmentRequests'])->name('enrollmentRequests');
                    Route::post('/enrollment-requests/{enrollmentRequest}/cancel-request', [GuestController::class, 'requestCancellation'])->name('enrollmentRequests.cancelRequest');
                    
                    // Guest payment routes
                    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
                    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
                }
                );

                // Enrollment and checkout routes — accessible to both guest AND student roles
                // (students need to enroll in additional courses after completing one)
                Route::middleware(['auth:student', 'nocache'])->group(function (): void {
                    Route::post('/enroll/{course}', [GuestController::class , 'enroll'])->name('enroll');
                    
                    // Enrollment-specific GCash checkout
                    Route::get('/payment/{enrollment_request_id}', [GuestController::class, 'showPayment'])->name('payment.show');
                    Route::post('/payment/{enrollment_request_id}/submit', [GuestController::class, 'submitPayment'])->name('payment.submit');
                }
                );

                // AUD-002: Secure Document Access
                Route::middleware(['auth:student,admin', 'nocache'])->group(function (): void {
                    Route::get('/license/{student}', [\App\Http\Controllers\StorageController::class, 'streamLicense'])->name('storage.license');
                    Route::get('/credential/{enrollment}', [\App\Http\Controllers\StorageController::class, 'streamCredential'])->name('storage.credential');
                    Route::get('/gcash-qr/{gcashSetting}', [\App\Http\Controllers\StorageController::class, 'streamGcashQr'])->name('storage.gcash-qr');
                    Route::get('/receipt', [\App\Http\Controllers\StorageController::class, 'streamReceipt'])->name('storage.receipt');
                });
            }
            );

            // Shared Vehicle Image Access
            Route::middleware(['auth:student,admin', 'nocache'])->group(function (): void {
                Route::get('/vehicle-image/{image}', [\App\Http\Controllers\StorageController::class, 'streamVehicleImage'])
                    ->name('storage.vehicle-image')
                    ->withoutScopedBindings();
            });

            Route::prefix('admin')->name('admin.')->middleware(['school.context', 'auth:admin', 'redirect.system.admin', 'branch.access', 'nocache'])->group(function (): void {
            // Routes that need ajax middleware (existing modal-based pages)
            Route::middleware(['ajax'])->group(function () {
                    Route::get('/', [AdminController::class , 'dashboard'])->name('dashboard');

                    Route::get('/user-management', [AdminController::class , 'userManagement'])->name('userManagement');


                    Route::post('/store-account', [AdminController::class , 'storeAccount'])->name('storeAccount');

                    Route::put('/students/{id}', [AdminController::class , 'updateStudent'])->name('students.update');
                    Route::patch('/students/{id}/toggle-status', [AdminController::class , 'toggleStudentStatus'])->name('students.toggleStatus');

                    Route::post('/instructors', [AdminController::class , 'storeAccount'])->name('instructors.store');
                    Route::put('/instructors/{id}', [AdminController::class , 'updateInstructor'])->name('instructors.update');
                    Route::patch('/instructors/{id}/toggle-status', [AdminController::class, 'toggleInstructorStatus'])->name('instructors.toggleStatus');
                    Route::post('/instructors/{id}/verify', [AdminController::class, 'verifyInstructor'])->name('instructors.verify');
                    Route::patch('/instructors/{id}/availability', [AdminController::class, 'toggleInstructorAvailability'])->name('instructors.toggleAvailability');

                    // Schedule management (unified system: admin creates, can pre-assign, instructors self-select remaining spots)
                    Route::get('/schedules', [AdminController::class , 'schedules'])->name('schedules');
                    Route::post('/schedules/create', [AdminController::class , 'createSchedule'])->name('schedules.create');
                    Route::put('/schedules/{id}', [AdminController::class , 'updateSchedule'])->name('schedules.update');
                    Route::delete('/schedules/{id}', [AdminController::class , 'deleteSchedule'])->name('schedules.delete');

                    // Instructor removal requests
                    Route::get('/removal-requests', [AdminController::class , 'removalRequests'])->name('removalRequests');
                    Route::post('/removal-requests/{id}/approve', [AdminController::class , 'approveRemovalRequest'])->name('removalRequests.approve');
                    Route::post('/removal-requests/{id}/reject', [AdminController::class , 'rejectRemovalRequest'])->name('removalRequests.reject');

                    // Course management with packages
                    Route::get('/courses', [AdminController::class , 'courses'])->name('courses');
                    Route::post('/courses', [AdminController::class , 'storeCourse'])->name('courses.store');
                    Route::put('/courses/{id}', [AdminController::class , 'updateCourse'])->name('courses.update');
                    Route::delete('/courses/{id}', [AdminController::class , 'deleteCourse'])->name('courses.delete');

                    Route::post('/courses/{courseId}/packages', [AdminController::class , 'storePackage'])->name('courses.packages.store');
                    Route::put('/courses/{courseId}/packages/{packageId}', [AdminController::class , 'updatePackage'])->name('courses.packages.update');
                    Route::delete('/courses/{courseId}/packages/{packageId}', [AdminController::class , 'deletePackage'])->name('courses.packages.delete');

                    // School settings
                    Route::get('/settings', [AdminController::class , 'settings'])->name('settings');
                    Route::post('/settings', [AdminController::class , 'updateSettings'])->name('settings.update');

                    // Invitation management
                    Route::name('invitations.')->group(function () {
                        Route::post('/invitations/{invitation}/resend', [AdminController::class, 'resendInvitation'])->name('resend');
                        Route::delete('/invitations/{invitation}/cancel', [AdminController::class, 'cancelInvitation'])->name('cancel');
                    });

                    // Branch management
                    Route::get('/branches', [BranchController::class , 'index'])->name('branches.index');
                    Route::post('/branches', [BranchController::class , 'store'])->name('branches.store');
                    Route::put('/branches/{id}', [BranchController::class , 'update'])->name('branches.update');
                    Route::patch('/branches/{id}/toggle', [BranchController::class , 'toggleActive'])->name('branches.toggle');
                    Route::delete('/branches/{id}', [BranchController::class , 'destroy'])->name('branches.destroy');

                    // Vehicle management
                    Route::prefix('vehicles')->name('vehicles.')->group(function () {
                        Route::get('/', [\App\Http\Controllers\VehicleController::class, 'index'])->name('index');
                        Route::post('/', [\App\Http\Controllers\VehicleController::class, 'store'])->name('store');
                        Route::put('/{vehicle}', [\App\Http\Controllers\VehicleController::class, 'update'])->name('update');
                        Route::delete('/{vehicle}', [\App\Http\Controllers\VehicleController::class, 'destroy'])->name('destroy');
                        Route::patch('/{vehicle}/toggle-status', [\App\Http\Controllers\VehicleController::class, 'toggleStatus'])->name('toggleStatus');
                        
                        Route::post('/categories', [\App\Http\Controllers\VehicleController::class, 'storeCategory'])->name('categories.store');
                        Route::put('/categories/{category}', [\App\Http\Controllers\VehicleController::class, 'updateCategory'])->name('categories.update');
                        Route::delete('/categories/{category}', [\App\Http\Controllers\VehicleController::class, 'destroyCategory'])->name('categories.destroy');
                        Route::delete('/{vehicle}/images/{image}', [\App\Http\Controllers\VehicleController::class, 'deleteImage'])->name('images.destroy');
                    });

                    // Reports & Analytics - Grouped for clarity and robust naming
                    Route::prefix('reports')->group(function () {
                            Route::get('/', [ReportController::class , 'index'])->name('reports.index');
                            Route::get('/students', [AdminController::class , 'studentReports'])->name('reports.students');
                            Route::get('/instructors', [AdminController::class , 'instructorReports'])->name('reports.instructors');
                            Route::get('/logs', [AdminController::class , 'logs'])->name('reports.logs');

                            }
                            );

                            Route::get('/profile', [AdminController::class , 'profile'])->name('profile');
                            Route::put('/profile', [AdminController::class , 'updateProfile'])->name('profile.update');
                            Route::post('/profile/picture', [AdminController::class , 'updateProfilePicture'])->name('profile.picture');

                            // Export routes
                            Route::prefix('exports')->name('exports.')->group(function () {
                            Route::get('/students/pdf', [ExportController::class , 'studentsPdf'])->name('students.pdf');
                            Route::get('/students/excel', [ExportController::class , 'studentsExcel'])->name('students.excel');
                            Route::get('/admins/pdf', [ExportController::class , 'adminsPdf'])->name('admins.pdf');
                            Route::get('/admins/excel', [ExportController::class , 'adminsExcel'])->name('admins.excel');
                            Route::get('/enrollments/pdf', [ExportController::class , 'enrollmentsPdf'])->name('enrollments.pdf');
                            Route::get('/enrollments/excel', [ExportController::class, 'enrollmentsExcel'])->name('enrollments.excel');
                            Route::get('/student/{student}/progress/pdf', [ExportController::class , 'studentProgressPdf'])->name('student.progress.pdf');
                            Route::get('/instructors/pdf', [ExportController::class , 'instructorsPdf'])->name('instructors.pdf');
                            Route::get('/instructors/excel', [ExportController::class , 'instructorsExcel'])->name('instructors.excel');
                            Route::get('/schedules/pdf', [ExportController::class , 'schedulesPdf'])->name('schedules.pdf');
                            Route::get('/payments/pdf', [ExportController::class , 'paymentsPdf'])->name('payments.pdf');
                            Route::get('/payments/excel', [ExportController::class , 'paymentsExcel'])->name('payments.excel');
                            Route::get('/verify-session-completion/excel', [ExportController::class , 'bookingsExcel'])->name('verify-session-completion.excel');
                            Route::get('/courses/excel', [ExportController::class , 'coursesExcel'])->name('courses.excel');
                            Route::get('/courses/pdf', [ExportController::class , 'coursesPdf'])->name('courses.pdf');
                        }
                        );

                        // Verify Session Completion management (matches page title)
                        Route::resource('verify-session-completion', BookingController::class)
                            ->names('verify-session-completion')
                            ->parameters(['verify-session-completion' => 'booking'])
                            ->except(['create', 'edit']);
                        Route::patch('/verify-session-completion/{booking}/status', [BookingController::class , 'updateStatus'])->name('verify-session-completion.updateStatus');

                        // Payments management (no separate create/edit views - handled via modals)
                        // Statistics route MUST be before the resource to avoid conflict with payments/{payment}
                        Route::get('/payments/statistics', [PaymentController::class , 'statistics'])->name('payments.statistics');
                        Route::resource('payments', PaymentController::class)->except(['create', 'edit', 'update']);

                        // Admin/Secretary management (school_admin only)
                        Route::middleware(['school.admin.only'])->group(function () {
                            Route::prefix('admin-management')->name('admin-management.')->group(function () {
                                    Route::get('/', [AdminManagementController::class , 'index'])->name('index');
                                    Route::post('/', [AdminManagementController::class , 'store'])->name('store');
                                    Route::put('/{targetAdmin}', [AdminManagementController::class , 'update'])->name('update');
                                    Route::patch('/{targetAdmin}/toggle-status', [AdminManagementController::class , 'toggleStatus'])->name('toggleStatus');
                                    Route::delete('/{targetAdmin}', [AdminManagementController::class , 'destroy'])->name('destroy');
                                }
                                );

                            // School-admin-only routes: settings, financial reports
                            // (Reports route handled in the main reports group to avoid URI collision)
                            }
                            );

                        // New LMS routes WITH ajax middleware for layout consistency
                        // Enrollment management (combining enrollment requests and enrollments)
                        Route::middleware(['ajax'])->group(function () {
                    Route::prefix('enrollments')->name('enrollments.')->group(function () {
                            Route::get('/', [EnrollmentRequestController::class , 'index'])->name('index');
                            Route::get('/{enrollmentRequest}', [EnrollmentRequestController::class , 'show'])->name('show');
                            Route::post('/bulk-approve', [EnrollmentRequestController::class , 'bulkApprove'])->name('bulkApprove');
                            Route::post('/bulk-reject', [EnrollmentRequestController::class , 'bulkReject'])->name('bulkReject');
                            Route::post('/{enrollmentRequest}/approve', [EnrollmentRequestController::class , 'approve'])->name('approve');
                            Route::post('/{enrollmentRequest}/reject', [EnrollmentRequestController::class , 'reject'])->name('reject');
                            Route::post('/{enrollmentRequest}/complete', [EnrollmentRequestController::class , 'complete'])->name('complete');
                            Route::post('/{enrollmentRequest}/cancel', [EnrollmentRequestController::class , 'cancel'])->name('cancel');
                            Route::post('/{enrollmentRequest}/payment-status', [EnrollmentRequestController::class , 'updatePaymentStatus'])->name('paymentStatus');
                            Route::post('/{enrollmentRequest}/theoretical-passed', [EnrollmentRequestController::class , 'markTheoreticalPassed'])->name('theoreticalPassed');

                            // Student license verification
                            Route::get('/student/{student}/view-license', [EnrollmentRequestController::class , 'viewLicense'])->name('viewLicense');
                            Route::post('/student/{student}/verify-license', [EnrollmentRequestController::class , 'verifyLicense'])->name('verifyLicense');
                            Route::post('/student/{student}/reject-license', [EnrollmentRequestController::class , 'rejectLicense'])->name('rejectLicense');

                            // API routes for the Quick-Verify Modal
                            Route::prefix('api')->name('api.')->group(function () {
                                Route::get('/{enrollmentRequest}', [EnrollmentRequestController::class, 'apiShow'])->name('show');
                                Route::post('/{enrollmentRequest}/verify-payment', [EnrollmentRequestController::class, 'verifyPayment'])->name('verify-payment');
                                Route::post('/{enrollmentRequest}/unified-approve', [EnrollmentRequestController::class, 'unifiedApprove'])->name('unified-approve');
                                Route::post('/{enrollmentRequest}/verify-license', [EnrollmentRequestController::class, 'apiVerifyLicense'])->name('verify-license');
                            });

                            // View payment proof
                            Route::get('/{enrollmentRequest}/view-payment-proof', [EnrollmentRequestController::class, 'viewPaymentProof'])->name('view-payment-proof');
                            Route::get('/{enrollmentRequest}/view-credential', [EnrollmentRequestController::class, 'viewCredential'])->name('view-credential');
                        });


                        // Safety redirect for singular routes (admin/enrollment/* -> admin/enrollments)
                        Route::get('/enrollment/{any?}', function ($school, $any = null) {
                            return redirect()->route('schools.admin.enrollments.index', ['school' => $school]);
                        })->where('any', '.*');

                        // Theoretical training management
                        Route::prefix('theoretical')->name('theoretical.')->group(function () {
                            Route::get('/', [TheoreticalCompletionController::class , 'index'])->name('index');
                            Route::get('/stats/overview', [TheoreticalCompletionController::class , 'stats'])->name('stats');
                            Route::get('/{enrollment}', [TheoreticalCompletionController::class , 'show'])->name('show');
                            Route::post('/mark-passed', [TheoreticalCompletionController::class , 'markAsPassed'])->name('markAsPassed');
                            Route::post('/{enrollment}/complete', [TheoreticalCompletionController::class , 'complete'])->name('complete');
                            Route::post('/{enrollment}/revoke', [TheoreticalCompletionController::class , 'revoke'])->name('revoke');
                        }
                        );

                        // Session completions management (View sessions logged by instructors)
                        Route::prefix('sessions')->name('sessions.')->group(function () {
                            Route::get('/', [SessionCompletionController::class , 'index'])->name('index');
                            Route::get('/{sessionCompletion}', [SessionCompletionController::class , 'show'])->name('show');
                            Route::delete('/{sessionCompletion}', [SessionCompletionController::class , 'destroy'])->name('destroy');
                            Route::get('/enrollment/{enrollment}/stats', [SessionCompletionController::class , 'enrollmentStats'])->name('enrollmentStats');
                        }
                        );

                        // Phase progression management (Admin reviews student phase transitions)
                        Route::prefix('phase-progressions')->name('phase-progressions.')->group(function () {
                            Route::get('/', [PhaseProgressionController::class , 'index'])->name('index');
                            Route::post('/{phaseProgression}/approve', [PhaseProgressionController::class , 'approve'])->name('approve');
                            Route::post('/{phaseProgression}/reject', [PhaseProgressionController::class , 'reject'])->name('reject');
                        }
                        );

                        // Course modules and lessons management (LMS content)
                        Route::get('/materials', [CourseModuleController::class , 'materialsHub'])->name('materials.index');

                        Route::prefix('courses/{course}')->name('courses.')->group(function () {
                            // Modules
                            Route::prefix('modules')->name('modules.')->group(function () {
                                    Route::get('/', [CourseModuleController::class , 'index'])->name('index');
                                    Route::get('/create', [CourseModuleController::class , 'create'])->name('create');
                                    Route::post('/', [CourseModuleController::class , 'store'])->name('store');
                                    Route::get('/{module}', [CourseModuleController::class , 'show'])->name('show');
                                    Route::get('/{module}/edit', [CourseModuleController::class , 'edit'])->name('edit');
                                    Route::put('/{module}', [CourseModuleController::class , 'update'])->name('update');
                                    Route::delete('/{module}', [CourseModuleController::class , 'destroy'])->name('destroy');
                                    Route::post('/reorder', [CourseModuleController::class , 'reorder'])->name('reorder');
                                    Route::post('/{module}/duplicate', [CourseModuleController::class , 'duplicate'])->name('duplicate');

                                    // Lessons within modules
                                    Route::prefix('{module}/lessons')->name('lessons.')->group(function () {
                                            Route::get('/', [ModuleLessonController::class , 'index'])->name('index');
                                            Route::get('/create', [ModuleLessonController::class , 'create'])->name('create');
                                            Route::post('/', [ModuleLessonController::class , 'store'])->name('store');
                                            Route::get('/{lesson}', [ModuleLessonController::class , 'show'])->name('show');
                                            Route::get('/{lesson}/edit', [ModuleLessonController::class , 'edit'])->name('edit');
                                            Route::put('/{lesson}', [ModuleLessonController::class , 'update'])->name('update');
                                            Route::delete('/{lesson}', [ModuleLessonController::class , 'destroy'])->name('destroy');
                                            Route::post('/reorder', [ModuleLessonController::class , 'reorder'])->name('reorder');
                                        }
                                        );
                                    }
                                    );
                                }
                                );
                            }
                            ); // end ajax middleware for LMS routes
                            
                        }
                        ); // end admin group

                            Route::post('/logout', [AuthController::class , 'logout'])->name('logout');
                        }
                        );

                        Route::prefix('instructor')->name('instructor.')->middleware(['auth:instructor', 'nocache'])->group(function (): void {
            // Routes with ajax middleware (existing modal pages)
            Route::middleware(['ajax'])->group(function () {
                    Route::get('/', [InstructorController::class , 'dashboard'])->name('dashboard');

                    Route::get('/my-schedule', [InstructorTimeSlotController::class , 'mySchedule'])->name('schedule');
                    Route::post('/timeslots/{id}/toggle', [InstructorTimeSlotController::class , 'toggle'])->name('timeslots.toggle');
                    Route::post('/timeslots/{id}/request-removal', [InstructorTimeSlotController::class , 'requestRemoval'])->name('timeslots.requestRemoval');

                    Route::get('/profile', [InstructorTimeSlotController::class , 'profile'])->name('profile');
                    Route::put('/profile', [InstructorTimeSlotController::class , 'updateProfile'])->name('profile.update');
                    Route::post('/profile/picture', [InstructorTimeSlotController::class , 'updateProfilePicture'])->name('profile.picture');
                    Route::post('/upload-license', [InstructorTimeSlotController::class , 'uploadLicense'])->name('uploadLicense');

                    // Instructor attendance and feedback (used from schedule page)
                    Route::post('/bookings/{booking}/attendance', [InstructorTimeSlotController::class , 'updateAttendance'])->name('bookings.attendance');
                    Route::post('/bookings/{booking}/feedback', [InstructorTimeSlotController::class , 'updateFeedback'])->name('bookings.feedback');

                    // Instructor lesson details
                    Route::get('/lessons/{booking}', [InstructorTimeSlotController::class , 'getLessonDetails'])->name('lessons.details');
                    Route::post('/lessons/{booking}/update', [InstructorTimeSlotController::class , 'updateLessonDetails'])->name('lessons.update');

                    // Instructor students
                    Route::get('/students', [InstructorController::class , 'myStudents'])->name('students.index');
                    Route::get('/students/{id}', [InstructorController::class , 'showStudent'])->name('students.show');

                    // Instructor progress updates
                    Route::get('/progress', [ProgressController::class , 'index'])->name('progress.index');
                    Route::get('/progress/create', [ProgressController::class , 'create'])->name('progress.create');
                    Route::post('/progress', [ProgressController::class , 'store'])->name('progress.store');
                    Route::get('/progress/{progress}', [ProgressController::class , 'show'])->name('progress.show');
                    Route::get('/progress/{progress}/edit', [ProgressController::class , 'edit'])->name('progress.edit');
                    Route::put('/progress/{progress}', [ProgressController::class , 'update'])->name('progress.update');
                    Route::delete('/progress/{progress}', [ProgressController::class , 'destroy'])->name('progress.destroy');

                    // Instructor performance reports
                    Route::get('/reports', [InstructorController::class , 'reports'])->name('reports');

                    // Instructor grade management
                    Route::get('/grades', [InstructorController::class , 'grades'])->name('grades');

                    // Instructor session logging (inside ajax middleware for proper AJAX loading)
                    Route::prefix('sessions')->name('sessions.')->group(function () {
                            Route::get('/', [SessionCompletionController::class , 'index'])->name('index');
                            Route::get('/create', [SessionCompletionController::class , 'create'])->name('create');
                            Route::post('/', [SessionCompletionController::class , 'store'])->name('store');
                            Route::get('/{sessionCompletion}', [SessionCompletionController::class , 'show'])->name('show');
                            Route::get('/{sessionCompletion}/edit', [SessionCompletionController::class , 'edit'])->name('edit');
                            Route::put('/{sessionCompletion}', [SessionCompletionController::class , 'update'])->name('update');
                            Route::delete('/{sessionCompletion}', [SessionCompletionController::class , 'destroy'])->name('destroy');
                            Route::get('/enrollment/{enrollment}/stats', [SessionCompletionController::class , 'enrollmentStats'])->name('enrollmentStats');
                        }
                        );

                        // Instructor export routes (PDF & Excel)
                        Route::prefix('exports')->name('exports.')->group(function () {
                            Route::get('/students/pdf', [ExportController::class , 'instructorStudentsPdf'])->name('students.pdf');
                            Route::get('/students/excel', [ExportController::class , 'instructorStudentsExcel'])->name('students.excel');
                            Route::get('/sessions/pdf', [ExportController::class , 'instructorSessionsPdf'])->name('sessions.pdf');
                            Route::get('/sessions/excel', [ExportController::class , 'instructorSessionsExcel'])->name('sessions.excel');
                            Route::get('/grades/pdf', [ExportController::class , 'instructorGradesPdf'])->name('grades.pdf');
                            Route::get('/grades/excel', [ExportController::class , 'instructorGradesExcel'])->name('grades.excel');
                            Route::get('/reports/pdf', [ExportController::class , 'instructorReportsPdf'])->name('reports.pdf');
                        }
                        );
                    }
                    );

                    // LMS routes with ajax middleware for layout consistency
                    Route::middleware(['ajax'])->group(function () {
                    Route::get('/materials', [CourseModuleController::class , 'materialsHub'])->name('materials.index');

                    // Question Bank
                    Route::prefix('questions')->name('questions.')->group(function () {
                        Route::get('/', [QuestionBankController::class, 'index'])->name('index');
                        Route::get('/create', [QuestionBankController::class, 'create'])->name('create');
                        Route::post('/', [QuestionBankController::class, 'store'])->name('store');
                        Route::get('/{question}/edit', [QuestionBankController::class, 'edit'])->name('edit');
                        Route::put('/{question}', [QuestionBankController::class, 'update'])->name('update');
                        Route::delete('/{question}', [QuestionBankController::class, 'destroy'])->name('destroy');
                        Route::get('/ajax/lessons/{course}', [QuestionBankController::class, 'getLessons'])->name('ajax.lessons');
                    });

                    Route::prefix('theoretical')->name('theoretical.')->group(function () {
                            Route::get('/', [TheoreticalCompletionController::class , 'index'])->name('index');
                            Route::get('/{enrollment}', [TheoreticalCompletionController::class , 'show'])->name('show');
                            Route::post('/mark-passed', [TheoreticalCompletionController::class , 'markAsPassed'])->name('markAsPassed');
                        }
                        );

                        // Instructor course modules (Manage course content)
                        Route::prefix('courses/{course}/modules')->name('courses.modules.')->group(function () {
                            Route::get('/', [CourseModuleController::class , 'index'])->name('index');
                            Route::get('/create', [CourseModuleController::class , 'create'])->name('create');
                            Route::post('/', [CourseModuleController::class , 'store'])->name('store');
                            Route::post('/reorder', [CourseModuleController::class , 'reorder'])->name('reorder');
                            Route::get('/{module}/edit', [CourseModuleController::class , 'edit'])->name('edit');
                            Route::put('/{module}', [CourseModuleController::class , 'update'])->name('update');
                            Route::delete('/{module}', [CourseModuleController::class , 'destroy'])->name('destroy');
                            Route::post('/{module}/duplicate', [CourseModuleController::class , 'duplicate'])->name('duplicate');
                            Route::get('/{module}', [CourseModuleController::class , 'show'])->name('show');

                            Route::prefix('{module}/lessons')->name('lessons.')->group(function () {
                                    Route::get('/', [ModuleLessonController::class , 'index'])->name('index');
                                    Route::get('/create', [ModuleLessonController::class , 'create'])->name('create');
                                    Route::post('/', [ModuleLessonController::class , 'store'])->name('store');
                                    Route::post('/reorder', [ModuleLessonController::class , 'reorder'])->name('reorder');
                                    Route::get('/{lesson}/edit', [ModuleLessonController::class , 'edit'])->name('edit');
                                    Route::put('/{lesson}', [ModuleLessonController::class , 'update'])->name('update');
                                    Route::delete('/{lesson}', [ModuleLessonController::class , 'destroy'])->name('destroy');
                                    Route::get('/{lesson}', [ModuleLessonController::class , 'show'])->name('show');
                                }
                                );

                                // Assessment Question management
                                Route::prefix('{module}/assessments')->name('assessments.')->group(function () {
                                    Route::get('/manage', [AssessmentController::class, 'manage'])->name('manage');
                                    Route::post('/add', [AssessmentController::class, 'addQuestion'])->name('add');
                                    Route::post('/add-multiple', [AssessmentController::class, 'addMultipleQuestions'])->name('add_multiple');
                                    Route::delete('/{question}/remove', [AssessmentController::class, 'removeQuestion'])->name('remove');
                                    Route::post('/reorder', [AssessmentController::class, 'reorder'])->name('reorder');
                                });
                            }
                            );
                        }
                        ); // end ajax middleware for instructor LMS routes
                
                        Route::post('/logout', [AuthController::class , 'logout'])->name('logout');
                    }
                    );

                    Route::prefix('student')->name('student.')->middleware(['auth:student', 'student.role', 'nocache'])->group(function (): void {
            // Routes with ajax middleware (existing pages)
            Route::middleware(['ajax'])->group(function () {
                    Route::get('/', [StudentController::class , 'dashboard'])->name('dashboard');

                    Route::get('/profile', [StudentController::class , 'profile'])->name('profile');
                    Route::put('/profile', [StudentController::class , 'updateProfile'])->name('profile.update');
                    Route::post('/profile/picture', [StudentController::class , 'updateProfilePicture'])->name('profile.picture');

                    // Student courses
                    Route::get('/courses', [CourseController::class , 'index'])->name('courses.index');
                    Route::get('/courses/{course}', [CourseController::class , 'show'])->name('courses.show');
                    Route::post('/upload-license', [StudentController::class, 'uploadLicense'])->name('uploadLicense');
                    Route::post('/enroll/{course}', [StudentController::class, 'enroll'])->name('enroll');

                    // Booking queue management (used in schedule page)
                    Route::post('/bookings', [BookingController::class , 'store'])->name('bookings.store');
                    Route::post('/bookings/{booking}/confirm', [BookingController::class , 'confirmBooking'])->name('bookings.confirm');
                    Route::delete('/bookings/{booking}/queue', [BookingController::class , 'removeFromQueue'])->name('bookings.removeQueue');
                    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancelBooking'])->name('bookings.cancel');

                    // Student progress (single page view - no individual progress detail page)
                    Route::get('/progress', [ProgressController::class , 'index'])->name('progress.index');

                    // Student payments
                    Route::get('/payments', [PaymentController::class , 'index'])->name('payments.index');
                    Route::get('/payments/{payment}', [PaymentController::class , 'show'])->name('payments.show');
                    Route::post('/payments', [PaymentController::class , 'store'])->name('payments.store');
                    // Dedicated payment page for (re-)enrollment checkout (not the My Payments list)
                    Route::get('/payment/{enrollment_request_id}', [StudentController::class, 'showPayment'])->name('payment.show');
                    Route::post('/payment/{enrollment_request_id}/submit', [StudentController::class, 'submitPayment'])->name('payment.submit');

                    // Student schedule
                    Route::get('/schedule', [StudentController::class , 'schedule'])->name('schedule');

                    // Student's current course (single enrollment view)
                    Route::get('/my-course', [StudentController::class , 'myCourse'])->name('my-course');

                    // Student's progress overview
                    Route::get('/my-progress', [StudentController::class , 'myProgress'])->name('my-progress');
                }
                );

                // LMS routes with ajax middleware for layout consistency
                Route::middleware(['ajax'])->group(function () {
                    // Student course modules (View enrolled course content)
                    Route::prefix('courses/{course}/modules')->name('courses.modules.')->group(function () {
                            Route::get('/', [CourseModuleController::class , 'index'])->name('index');
                            Route::get('/{module}', [CourseModuleController::class , 'show'])->name('show');

                            Route::prefix('{module}/lessons')->name('lessons.')->group(function () {
                                    Route::get('/', [ModuleLessonController::class , 'index'])->name('index');
                                    Route::post('/{lesson}/complete', [ModuleLessonController::class , 'complete'])->name('complete');
                                    Route::post('/{lesson}/complete', [ModuleLessonController::class , 'complete'])->name('complete');
                                    Route::get('/{lesson}', [ModuleLessonController::class , 'show'])->name('show');
                                }
                                );

                            // Student Assessment
                            Route::get('/{module}/assessment', [CourseModuleController::class, 'takeAssessment'])->name('assessment.take');
                            }
                            );
                        }
                        ); // end ajax middleware for student LMS routes
                
                        Route::post('/logout', [AuthController::class , 'logout'])->name('logout');
                    }
                    );
                });