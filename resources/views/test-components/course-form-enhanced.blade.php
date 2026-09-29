{{-- TEST COMPONENT: Enhanced Course Form --}}
{{-- Location: test-components/course-form-enhanced.blade.php --}}
{{-- Test this before integrating into main system --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test: Enhanced Course Form</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            padding: 40px 20px;
        }

        .test-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }

        .test-header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .test-header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .form-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .form-section {
            margin-bottom: 35px;
        }

        .form-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-icon {
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .required {
            color: #ef4444;
            margin-left: 2px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .radio-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .radio-option {
            position: relative;
        }

        .radio-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .radio-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }

        .radio-option input[type="radio"]:checked + .radio-label {
            border-color: #667eea;
            background: #f0f4ff;
            color: #667eea;
        }

        .radio-label:hover {
            border-color: #667eea;
        }

        .radio-indicator {
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            position: relative;
            transition: all 0.3s ease;
        }

        .radio-option input[type="radio"]:checked + .radio-label .radio-indicator {
            border-color: #667eea;
            background: #667eea;
        }

        .radio-indicator::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: white;
            transition: transform 0.3s ease;
        }

        .radio-option input[type="radio"]:checked + .radio-label .radio-indicator::after {
            transform: translate(-50%, -50%) scale(1);
        }

        .help-text {
            font-size: 13px;
            color: #6b7280;
            margin-top: 6px;
        }

        .hours-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .hour-input-group {
            position: relative;
        }

        .hour-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            display: block;
        }

        .hour-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .hour-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .hour-suffix {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13px;
            color: #9ca3af;
            font-weight: 600;
            margin-top: 10px;
        }

        .total-hours {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-top: 20px;
        }

        .total-hours-label {
            font-size: 13px;
            opacity: 0.9;
            margin-bottom: 8px;
        }

        .total-hours-value {
            font-size: 36px;
            font-weight: 700;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid #e5e7eb;
        }

        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            flex: 1;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            padding: 14px 25px;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .info-box-title {
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .info-box-text {
            font-size: 13px;
            color: #1e3a8a;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .form-row,
            .hours-grid {
                grid-template-columns: 1fr;
            }

            .radio-group {
                flex-direction: column;
            }

            .radio-label {
                width: 100%;
            }
        }

        .output-json {
            background: #1f2937;
            color: #10b981;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            max-height: 400px;
            overflow-y: auto;
        }

        .output-title {
            color: #fbbf24;
            margin-bottom: 10px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="test-header">
            <h1>Enhanced Course Form - Test Component</h1>
            <p>Testing new course structure with license types and phase hours</p>
        </div>

        <div class="form-card">
            <form id="courseForm">
                {{-- Basic Information --}}
                <div class="form-section">
                    <div class="section-title">
                        <span class="section-icon"></span>
                        Basic Information
                    </div>

                    <div class="form-group">
                        <label class="form-label">Course Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g., Manual Transmission Driving" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" placeholder="Describe what students will learn in this course..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Price (₱) <span class="required">*</span></label>
                            <input type="number" name="price" class="form-control" placeholder="5000" step="0.01" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Max Students</label>
                            <input type="number" name="max_students" class="form-control" placeholder="20" value="20">
                        </div>
                    </div>
                </div>

                {{-- License Type --}}
                <div class="form-section">
                    <div class="section-title">
                        <span class="section-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg></span>
                        License Type
                    </div>

                    <div class="info-box">
                        <div class="info-box-title">About License Types:</div>
                        <div class="info-box-text">
                            <strong>Non-Professional:</strong> For personal/private use only (cannot drive for hire)<br>
                            <strong>Professional:</strong> For commercial/for-hire driving (Grab, taxi, delivery, etc.)<br>
                            <em style="color: #6b7280; font-size: 12px;">Tip: You can create separate courses for each type to better target your students</em>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">What license type does this course prepare students for? <span class="required">*</span></label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" name="license_type" id="license_nonpro" value="non-professional" checked>
                                <label for="license_nonpro" class="radio-label">
                                    <span class="radio-indicator"></span>
                                    <span>Non-Professional (Personal Use)</span>
                                </label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="license_type" id="license_pro" value="professional">
                                <label for="license_pro" class="radio-label">
                                    <span class="radio-indicator"></span>
                                    <span>Professional (For-Hire)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Vehicle Type --}}
                <div class="form-section">
                    <div class="section-title">
                        <span class="section-icon"></span>
                        Vehicle Type
                    </div>

                    <div class="form-group">
                        <label class="form-label">What vehicle will students train on? <span class="required">*</span></label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" name="vehicle_type" id="vehicle_manual" value="manual" checked>
                                <label for="vehicle_manual" class="radio-label">
                                    <span class="radio-indicator"></span>
                                    <span>Manual Transmission</span>
                                </label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="vehicle_type" id="vehicle_auto" value="automatic">
                                <label for="vehicle_auto" class="radio-label">
                                    <span class="radio-indicator"></span>
                                    <span>Automatic Transmission</span>
                                </label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="vehicle_type" id="vehicle_motorcycle" value="motorcycle">
                                <label for="vehicle_motorcycle" class="radio-label">
                                    <span class="radio-indicator"></span>
                                    <span>Motorcycle</span>
                                </label>
                            </div>
                        </div>
                        <p class="help-text">Note: Students with automatic-only training will get restriction code B1 (cannot drive manual vehicles)</p>
                    </div>
                </div>

                {{-- Course Purpose --}}
                <div class="form-section">
                    <div class="section-title">
                        <span class="section-icon"></span>
                        Course Purpose
                    </div>

                    <div class="form-group">
                        <label class="form-label">Who is this course designed for? <span class="required">*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="standard">Standard - New drivers (no experience)</option>
                            <option value="refresher">Refresher - Has license but needs practice</option>
                            <option value="defensive">Defensive Driving - Advanced techniques</option>
                        </select>
                        <p class="help-text">You can offer the same vehicle type for different student levels</p>
                    </div>
                </div>

                {{-- Training Hours --}}
                <div class="form-section">
                    <div class="section-title">
                        <span class="section-icon"></span>
                        Training Hours Required
                    </div>
                    <div class="info-box">
                        <div class="info-box-title">Set Your Training Hours:</div>
                        <div class="info-box-text">
                            <strong>Theoretical:</strong> Classroom training on traffic laws, road signs, and safety<br>
                            <strong>Practical:</strong> Behind-the-wheel training with instructor<br>
                            <em style="color: #6b7280; font-size: 12px;">The system suggests hours based on your selections, but you have full control to adjust them</em>
                        </div>
                    </div>

                    <div class="hours-grid">
                        <div class="hour-input-group">
                            <label class="hour-label">Theoretical Hours (Classroom) <span class="required">*</span></label>
                            <input type="number" name="theoretical_hours_required" id="theoreticalHours" class="hour-input" value="8" min="0" step="0.5" required>
                            <span class="hour-suffix">hrs</span>
                            <p class="help-text" style="font-size: 11px; margin-top: 4px; color: #9ca3af;">Adjust as needed for your school</p>
                        </div>

                        <div class="hour-input-group">
                            <label class="hour-label">Practical Hours (Driving) <span class="required">*</span></label>
                            <input type="number" name="practical_hours_required" id="practicalHours" class="hour-input" value="20" min="0" step="0.5" required>
                            <span class="hour-suffix">hrs</span>
                            <p class="help-text" style="font-size: 11px; margin-top: 4px; color: #9ca3af;">Adjust as needed for your school</p>
                        </div>
                    </div>div>
                    </div>

                    <div class="total-hours">
                        <div class="total-hours-label">Total Training Duration</div>
                        <div class="total-hours-value"><span id="totalHours">28</span> hours</div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset Form</button>
                    <button type="submit" class="btn btn-primary">Test Submit</button>
                </div>
            </form>

            {{-- Output Display --}}
            <div class="output-json" id="output" style="display: none;">
                <div class="output-title">Form Data (JSON):</div>
                <pre id="outputContent"></pre>
            </div>
        </div>
    </div>

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

        // Handle form submission
        document.getElementById('courseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            
            formData.forEach((value, key) => {
                data[key] = value;
            });

            // Add computed total
            data.total_duration_hours = parseFloat(data.theoretical_hours_required) + parseFloat(data.practical_hours_required);

            // Display output
            document.getElementById('outputContent').textContent = JSON.stringify(data, null, 2);
            document.getElementById('output').style.display = 'block';
            document.getElementById('output').scrollIntoView({ behavior: 'smooth' });

            console.log('Course Data:', data);
        });

        // Reset form
        function resetForm() {
            document.getElementById('courseForm').reset();
        // Suggest default hours based on vehicle type (admin can override)
        document.querySelectorAll('input[name="vehicle_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Only update if hours are at default values, otherwise preserve admin's custom values
                const currentTheoretical = parseFloat(document.getElementById('theoreticalHours').value);
                const currentPractical = parseFloat(document.getElementById('practicalHours').value);
                
                // Suggested hours based on common standards
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
                
                // Only suggest if fields are empty or at initial values
                if (currentTheoretical <= 8 && currentPractical <= 20) {
                    document.getElementById('theoreticalHours').value = suggestedTheory;
                    document.getElementById('practicalHours').value = suggestedPractical;
                }
                
                updateTotalHours();
            });
        });

        // Adjust suggested hours based on license type (admin can override)
        document.querySelectorAll('input[name="license_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const vehicleType = document.querySelector('input[name="vehicle_type"]:checked').value;
                
                // Base hours by vehicle type
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
                
                // Professional typically requires more training
                if (this.value === 'professional') {
                    document.getElementById('theoreticalHours').value = baseTheory + 2;
                    document.getElementById('practicalHours').value = basePractical + 10;
                } else {
                    document.getElementById('theoreticalHours').value = baseTheory;
                    document.getElementById('practicalHours').value = basePractical;
                }
                
                updateTotalHours();
            });
        });         }
                }
                updateTotalHours();
            });
        });
    </script>
</body>
</html>
