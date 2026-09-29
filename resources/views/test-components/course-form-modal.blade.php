{{-- MODAL VERSION: Enhanced Course Form --}}
{{-- This is ready to integrate into admin courses page --}}
{{-- Copy this into: resources/views/school/admin/courses.blade.php --}}

{{-- Add/Edit Course Modal --}}
<div class="modal fade" id="courseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 15px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px 15px 0 0; padding: 25px;">
                <h5 class="modal-title" id="courseModalLabel" style="font-weight: 700; font-size: 20px;">Add New Course</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="courseForm" method="POST">
                @csrf
                <input type="hidden" name="_method" value="POST" id="courseMethod">
                
                <div class="modal-body" style="padding: 30px; max-height: 70vh; overflow-y: auto;">
                    
                    {{-- Basic Information Section --}}
                    <div class="form-section" style="margin-bottom: 30px;">
                        <h6 style="font-weight: 700; color: #111827; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #e5e7eb;">
                            Basic Information
                        </h6>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 600; color: #374151;">
                                Course Title <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="title" id="courseTitle" class="form-control" placeholder="e.g., Manual Transmission Driving" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 600; color: #374151;">Description</label>
                            <textarea name="description" id="courseDescription" class="form-control" rows="3" placeholder="Describe what students will learn in this course..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="font-weight: 600; color: #374151;">
                                    Price (₱) <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="number" name="price" id="coursePrice" class="form-control" placeholder="5000" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="font-weight: 600; color: #374151;">Max Students</label>
                                <input type="number" name="max_students" id="courseMaxStudents" class="form-control" placeholder="20" value="20">
                            </div>
                        </div>
                    </div>

                    {{-- License Type Section --}}
                    <div class="form-section" style="margin-bottom: 30px;">
                        <h6 style="font-weight: 700; color: #111827; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e5e7eb;">
                            License Type
                        </h6>

                        <div class="alert alert-info" style="background: #eff6ff; border: none; border-left: 4px solid #3b82f6; font-size: 13px;">
                            <strong>Non-Professional:</strong> For personal/private use only (cannot drive for hire)<br>
                            <strong>Professional:</strong> For commercial/for-hire driving (Grab, taxi, delivery, etc.)
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 600; color: #374151;">
                                What license type does this course prepare students for? <span style="color: #ef4444;">*</span>
                            </label>
                            <div class="d-flex gap-3">
                                <div class="form-check" style="flex: 1;">
                                    <input class="form-check-input" type="radio" name="license_type" id="licenseNonPro" value="non-professional" checked>
                                    <label class="form-check-label" for="licenseNonPro" style="cursor: pointer;">
                                        Non-Professional (Personal Use)
                                    </label>
                                </div>
                                <div class="form-check" style="flex: 1;">
                                    <input class="form-check-input" type="radio" name="license_type" id="licensePro" value="professional">
                                    <label class="form-check-label" for="licensePro" style="cursor: pointer;">
                                        Professional (For-Hire)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Vehicle Type Section --}}
                    <div class="form-section" style="margin-bottom: 30px;">
                        <h6 style="font-weight: 700; color: #111827; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e5e7eb;">
                            Vehicle Type
                        </h6>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 600; color: #374151;">
                                What vehicle will students train on? <span style="color: #ef4444;">*</span>
                            </label>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="vehicle_type" id="vehicleManual" value="manual" checked>
                                        <label class="form-check-label" for="vehicleManual" style="cursor: pointer;">
                                            Manual Transmission
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="vehicle_type" id="vehicleAuto" value="automatic">
                                        <label class="form-check-label" for="vehicleAuto" style="cursor: pointer;">
                                            Automatic Transmission
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="vehicle_type" id="vehicleMotorcycle" value="motorcycle">
                                        <label class="form-check-label" for="vehicleMotorcycle" style="cursor: pointer;">
                                            Motorcycle
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Note: Students with automatic-only training will get restriction code B1</small>
                        </div>
                    </div>

                    {{-- Course Purpose Section --}}
                    <div class="form-section" style="margin-bottom: 30px;">
                        <h6 style="font-weight: 700; color: #111827; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e5e7eb;">
                            Course Purpose
                        </h6>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 600; color: #374151;">
                                Who is this course designed for? <span style="color: #ef4444;">*</span>
                            </label>
                            <select name="type" id="courseType" class="form-select" required>
                                <option value="standard">Standard - New drivers (no experience)</option>
                                <option value="refresher">Refresher - Has license but needs practice</option>
                                <option value="defensive">Defensive Driving - Advanced techniques</option>
                            </select>
                        </div>
                    </div>

                    {{-- Training Hours Section --}}
                    <div class="form-section" style="margin-bottom: 30px;">
                        <h6 style="font-weight: 700; color: #111827; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e5e7eb;">
                            Training Hours Required
                        </h6>

                        <div class="alert alert-info" style="background: #eff6ff; border: none; border-left: 4px solid #3b82f6; font-size: 13px;">
                            <strong>Theoretical:</strong> Classroom training on traffic laws, road signs, and safety<br>
                            <strong>Practical:</strong> Behind-the-wheel training with instructor
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="font-weight: 600; color: #374151;">
                                    Theoretical Hours (Classroom) <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="number" name="theoretical_hours_required" id="theoreticalHours" class="form-control" value="8" min="0" step="0.5" required>
                                <small class="text-muted">Adjust as needed for your school</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="font-weight: 600; color: #374151;">
                                    Practical Hours (Driving) <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="number" name="practical_hours_required" id="practicalHours" class="form-control" value="20" min="0" step="0.5" required>
                                <small class="text-muted">Adjust as needed for your school</small>
                            </div>
                        </div>

                        <div class="alert" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; text-align: center; padding: 20px; border-radius: 10px;">
                            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 5px;">Total Training Duration</div>
                            <div style="font-size: 32px; font-weight: 700;"><span id="totalHours">28</span> hours</div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer" style="padding: 20px 30px; border-top: 2px solid #e5e7eb;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; font-weight: 600;">
                        Save Course
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Modal-specific styles */
    #courseModal .form-control,
    #courseModal .form-select {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    #courseModal .form-control:focus,
    #courseModal .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    #courseModal .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    #courseModal .form-check-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
    }
</style>

<script>
    // Calculate total hours
    function updateTotalHours() {
        const theoretical = parseFloat(document.getElementById('theoreticalHours').value) || 0;
        const practical = parseFloat(document.getElementById('practicalHours').value) || 0;
        const total = theoretical + practical;
        document.getElementById('totalHours').textContent = total;
    }

    document.getElementById('theoreticalHours').addEventListener('input', updateTotalHours);
    document.getElementById('practicalHours').addEventListener('input', updateTotalHours);

    // Suggest hours based on vehicle type
    document.querySelectorAll('input[name="vehicle_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const currentTheoretical = parseFloat(document.getElementById('theoreticalHours').value);
            const currentPractical = parseFloat(document.getElementById('practicalHours').value);
            
            let suggestedTheory, suggestedPractical;
            if (this.value === 'manual') {
                suggestedTheory = 8;
                suggestedPractical = 20;
            } else if (this.value === 'automatic') {
                suggestedTheory = 8;
                suggestedPractical = 15;
            } else if (this.value === 'motorcycle') {
                suggestedTheory = 6;
                suggestedPractical = 12;
            }
            
            // Only suggest if at default values
            if (currentTheoretical <= 8 && currentPractical <= 20) {
                document.getElementById('theoreticalHours').value = suggestedTheory;
                document.getElementById('practicalHours').value = suggestedPractical;
            }
            
            updateTotalHours();
        });
    });

    // Adjust hours for professional license
    document.querySelectorAll('input[name="license_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const vehicleType = document.querySelector('input[name="vehicle_type"]:checked').value;
            
            let baseTheory, basePractical;
            if (vehicleType === 'manual') {
                baseTheory = 8;
                basePractical = 20;
            } else if (vehicleType === 'automatic') {
                baseTheory = 8;
                basePractical = 15;
            } else if (vehicleType === 'motorcycle') {
                baseTheory = 6;
                basePractical = 12;
            }
            
            if (this.value === 'professional') {
                document.getElementById('theoreticalHours').value = baseTheory + 2;
                document.getElementById('practicalHours').value = basePractical + 10;
            } else {
                document.getElementById('theoreticalHours').value = baseTheory;
                document.getElementById('practicalHours').value = basePractical;
            }
            
            updateTotalHours();
        });
    });

    // Open modal for adding new course
    function openAddCourseModal() {
        document.getElementById('courseModalLabel').textContent = 'Add New Course';
        document.getElementById('courseForm').reset();
        document.getElementById('courseMethod').value = 'POST';
        document.getElementById('courseForm').action = "{{ route('schools.admin.courses.store', $school) }}";
        updateTotalHours();
        new bootstrap.Modal(document.getElementById('courseModal')).show();
    }

    // Open modal for editing course
    function openEditCourseModal(course) {
        document.getElementById('courseModalLabel').textContent = 'Edit Course';
        document.getElementById('courseMethod').value = 'PUT';
        document.getElementById('courseForm').action = `/{{ $school->slug }}/admin/courses/${course.id}`;
        
        // Populate form fields
        document.getElementById('courseTitle').value = course.title;
        document.getElementById('courseDescription').value = course.description || '';
        document.getElementById('coursePrice').value = course.price;
        document.getElementById('courseMaxStudents').value = course.max_students;
        document.getElementById('courseType').value = course.type;
        
        // Set license type
        if (course.license_type) {
            document.getElementById(course.license_type === 'professional' ? 'licensePro' : 'licenseNonPro').checked = true;
        }
        
        // Set vehicle type
        if (course.vehicle_type === 'manual') {
            document.getElementById('vehicleManual').checked = true;
        } else if (course.vehicle_type === 'automatic') {
            document.getElementById('vehicleAuto').checked = true;
        } else if (course.vehicle_type === 'motorcycle') {
            document.getElementById('vehicleMotorcycle').checked = true;
        }
        
        // Set hours
        document.getElementById('theoreticalHours').value = course.theoretical_hours_required || 8;
        document.getElementById('practicalHours').value = course.practical_hours_required || 20;
        
        updateTotalHours();
        new bootstrap.Modal(document.getElementById('courseModal')).show();
    }
</script>
