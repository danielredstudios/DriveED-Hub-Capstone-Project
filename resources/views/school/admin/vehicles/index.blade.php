@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Vehicle Management')

@section('content')
<!-- Load Bootstrap Icons for this view -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    
    $primaryColor = $settings?->primary_color ?? '#6366f1'; 
    $secondaryColor = $settings?->secondary_color ?? '#4338ca';
    $useGradient = $settings?->use_gradient_header;

    $totalVehicles = $vehicles->count();
    $activeCount = $vehicles->where('status', 'active')->count();
    $inactiveCount = $vehicles->whereIn('status', ['maintenance', 'out_of_service'])->count();
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .vehicles-container { padding: 20px; margin: 20px auto; max-width: 1600px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid {{ $primaryColor }}; }
    .page-title { font-size: 1.75rem; font-weight: 600; color: #1f2937; margin: 0; }
    .page-subtitle { color: #6b7280; font-size: 0.9rem; margin-top: 5px; }

    /* Statistics Cards */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 24px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border-left: 5px solid transparent; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
    .stat-card.active::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: {{ $primaryColor }}08; }
    .stat-content { position: relative; z-index: 1; }
    .stat-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
    .stat-label { font-size: 0.85rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.025em; }
    .stat-value { font-size: 2rem; font-weight: 800; color: #111827; line-height: 1; }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .stat-card.total { border-left-color: #6366f1; }
    .stat-card.total .stat-icon { background: #eef2ff; color: #4338ca; }
    .stat-card.active-fleet { border-left-color: #10b981; }
    .stat-card.active-fleet .stat-icon { background: #ecfdf5; color: #047857; }
    .stat-card.inactive-fleet { border-left-color: #ef4444; }
    .stat-card.inactive-fleet .stat-icon { background: #fef2f2; color: #b91c1c; }

    /* Action Bar */
    .action-bar-container { margin-top: 20px; margin-bottom: 25px; background: #fff; padding: 20px; border-radius: 16px; border: 1px solid {{ $primaryColor }}15; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
    .search-wrapper { position: relative; display: flex; align-items: center; flex: 1; max-width: 450px; margin-top: 8px; }
    .search-wrapper input { width: 100% !important; height: 40px !important; padding: 10px 16px 10px 42px !important; border: 2px solid {{ $primaryColor }}15 !important; border-radius: 12px !important; font-size: 0.95rem !important; background: {{ $primaryColor }}05 !important; }
    .search-wrapper .search-icon { position: absolute; left: 15px; color: {{ $primaryColor }}80; width: 18px; }

    /* Filter Chips */
    .category-filter-bar { margin-top: 15px; padding-top: 15px; border-top: 1px solid #f3f4f6; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .category-filter-label { font-size: 0.8rem; font-weight: 600; color: #6b7280; text-transform: uppercase; }
    .category-chip { display: inline-flex; align-items: center; background: white; border: 1px solid #e5e7eb; padding: 6px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; color: #4b5563; transition: all 0.2s; cursor: pointer; }
    .category-chip:hover { border-color: {{ $primaryColor }}; color: {{ $primaryColor }}; }
    .category-chip.active { background: {{ $primaryColor }}10; border-color: {{ $primaryColor }}; color: {{ $primaryColor }}; }

    .btn-manage-cats { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 6px; cursor: pointer; transition: all 0.2s; margin-left: auto; }
    .btn-manage-cats:hover { background: #f1f5f9; color: {{ $primaryColor }}; border-color: {{ $primaryColor }}40; }

    /* Modal Styling */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; z-index: 1000; }
    .modal-overlay.active { display: flex; }
    .modal-content { background: white; border-radius: 16px; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4); overflow: hidden; display: flex; flex-direction: column; animation: modalEnter 0.3s ease-out; }
    @keyframes modalEnter { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .modal-header {
        @if($useGradient) background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else background: {{ $primaryColor }}; @endif
        color: white; padding: 24px 32px; position: relative;
    }
    .modal-header h5 { font-size: 1.25rem; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 12px; }
    .modal-body { padding: 32px; background: white; max-height: 75vh; overflow-y: auto; }
    .modal-footer { padding: 20px 32px; display: flex; gap: 12px; background: #f8fafc; border-top: 1px solid #f1f5f9; justify-content: flex-end; }
    .modal-footer button { position: relative; top: -15px; }

    /* Table Styles */
    .vehicle-table-card { background: white; border-radius: 16px; box-shadow: 0 2px 12px {{ $primaryColor }}10; overflow: hidden; border: 1px solid {{ $primaryColor }}08; }
    .vehicle-table { width: 100%; border-collapse: collapse; }
    .vehicle-table thead {
        @if($useGradient) background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else background: {{ $primaryColor }}; @endif
        color: white;
    }
    .vehicle-table thead th { padding: 16px 20px; text-align: left; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; color: white; letter-spacing: 0.05em; }
    .vehicle-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.2s; }
    .vehicle-table tbody tr:hover { background: #f9fafb; }
    .vehicle-table td { padding: 16px 20px; font-size: 0.95rem; color: #374151; vertical-align: middle; }

    /* Status Badges */
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 5px; }
    .status-active { background: #d1fae5; color: #065f46; }
    .status-maintenance { background: #fef3c7; color: #92400e; }
    .status-out_of_service { background: #fee2e2; color: #991b1b; }

    /* Action Buttons in Table */
    .btn-view-details { background: {{ $primaryColor }}08; color: {{ $primaryColor }}; padding: 6px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; border: 1px solid {{ $primaryColor }}20; transition: all 0.2s; cursor: pointer; }
    .btn-view-details:hover { background: {{ $primaryColor }}; color: white; border-color: {{ $primaryColor }}; }
    
    .btn-delete-row { background: #fef2f2; color: #ef4444; width: 32px; height: 32px; border-radius: 8px; border: 1px solid #fee2e2; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .btn-delete-row:hover { background: #ef4444; color: white; border-color: #ef4444; }

    /* Forms & UI Elements */
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.025em; }
    .form-group input, .form-group select, .form-group textarea {
        width: 100% !important; padding: 10px 16px !important; border: 2px solid #e2e8f0 !important;
        border-radius: 12px !important; font-size: 0.95rem !important; background: #fff !important; transition: all 0.2s;
    }
    .form-group input:focus, .form-group select:focus { border-color: {{ $primaryColor }} !important; box-shadow: 0 0 0 4px {{ $primaryColor }}10 !important; outline: none; }
    .form-group input:read-only { background: #f8fafc !important; border-color: #f1f5f9 !important; cursor: default; }

    .btn-primary, .btn-secondary, .btn-danger { height: 44px; padding: 0 20px; border-radius: 12px; font-size: 0.95rem; font-weight: 600; cursor: pointer; border: none; display: flex; align-items: center; gap: 8px; transition: all 0.2s; }
    .btn-secondary { background: #64748b !important; color: #ffffff !important; border: 1px solid #475569 !important; }
    .btn-secondary:hover { background: #64748b !important; color: #ffffff !important; transform: none !important; opacity: 0.9 !important; }
    .btn-danger { background: #fef2f2; color: #ef4444; border: 1px solid #fee2e2; }
    .btn-danger:hover { background: #ef4444; color: white; }
    .btn-deactivate { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; height: 34px; padding: 0 12px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 5px; transition: all 0.2s; }
    .btn-deactivate:hover { transform: translateY(-1px); filter: brightness(0.95); }
    .btn-activate { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; height: 34px; padding: 0 12px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 5px; transition: all 0.2s; }
    .btn-activate:hover { transform: translateY(-1px); filter: brightness(0.95); }
    .btn-primary { 
        @if($useGradient) background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else background: {{ $primaryColor }}; @endif
        color: white; box-shadow: 0 4px 12px {{ $primaryColor }}30;
    }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px {{ $primaryColor }}40; opacity: 0.95; }

    /* Multi-Image Gallery Styling */
    .image-gallery-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
    .gallery-item { position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 1; background: #f1f5f9; border: 1px solid #e2e8f0; }
    .gallery-item img { width: 100%; height: 100%; object-fit: cover; cursor: pointer; }
    .btn-remove-img { position: absolute; top: 5px; right: 5px; width: 24px; height: 24px; background: rgba(239, 68, 68, 0.9); color: white; border: none; border-radius: 50%; display: none; align-items: center; justify-content: center; cursor: pointer; font-size: 0.75rem; transition: all 0.2s; }
    .edit-mode .btn-remove-img { display: flex; }
    .btn-remove-img:hover { background: #ef4444; transform: scale(1.1); }

    .upload-box { display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; cursor: pointer; transition: all 0.2s; text-align: center; color: #94a3b8; padding: 10px; }
    .upload-box:hover { border-color: {{ $primaryColor }}; background: {{ $primaryColor }}05; color: {{ $primaryColor }}; }
    .upload-box i { font-size: 1.5rem; margin-bottom: 4px; }
    .upload-box span { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }

    .view-mode .edit-only { display: none !important; }
    .edit-mode .view-only { display: none !important; }
    .view-only-text { padding: 10px 0; font-weight: 600; color: #1e293b; display: block; }
</style>

<div class="vehicles-container">
    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Vehicle Management</h1>
            <p class="page-subtitle">Track your school's fleet, maintenance status, and vehicle assignments for {{ $school->name }}</p>
        </div>
        <div class="header-actions">
            <button class="btn-primary" onclick="openVehicleModal('createVehicleModal')">
                <i class="bi bi-plus-lg"></i> Add New Vehicle
            </button>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="stats-grid">
        <div class="stat-card total active" onclick="filterFleet('all', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div><div class="stat-label">Total Fleet</div><div class="stat-value">{{ $totalVehicles }}</div></div>
                    <div class="stat-icon"><i class="bi bi-car-front"></i></div>
                </div>
                <div style="font-size: 0.85rem; color: #6b7280;">All registered vehicles</div>
            </div>
        </div>
        <div class="stat-card active-fleet" onclick="filterFleet('active', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div><div class="stat-label">Active</div><div class="stat-value">{{ $activeCount }}</div></div>
                    <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                </div>
                <div style="font-size: 0.85rem; color: #6b7280;">In operation</div>
            </div>
        </div>
        <div class="stat-card inactive-fleet" onclick="filterFleet('inactive', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div><div class="stat-label">Inactive</div><div class="stat-value">{{ $inactiveCount }}</div></div>
                    <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
                </div>
                <div style="font-size: 0.85rem; color: #6b7280;">Maintenance / Out of service</div>
            </div>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="action-bar-container">
        <div class="search-box">
            <label style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.05em;">Search Fleet</label>
            <div class="search-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="vehicleSearch" placeholder="Search by model, plate, or branch..." onkeyup="filterVehicleTable()">
            </div>
        </div>

        <div class="category-filter-bar">
            <div class="category-filter-label"><i class="bi bi-tags"></i> Fleet Categories:</div>
            <div class="category-chip active" onclick="filterCategory('all', this)">All</div>
            @foreach($categories as $category)
                <div class="category-chip" onclick="filterCategory('{{ $category->name }}', this)">{{ $category->name }}</div>
            @endforeach
            
            <button class="btn-manage-cats" onclick="openVehicleModal('manageCategoriesModal')">
                <i class="bi bi-gear-fill"></i> Manage Categories
            </button>
        </div>
    </div>

    {{-- Table Section --}}
    @if($vehicles->isEmpty())
        <div style="text-align: center; padding: 80px 20px; color: #94a3b8;">
            <i class="bi bi-car-front" style="font-size: 4rem; opacity: 0.3;"></i>
            <h3 style="margin-top: 20px; color: #64748b;">No Vehicles Registered</h3>
            <p>Start managing your fleet by adding your first vehicle.</p>
        </div>
    @else
        <div class="vehicle-table-card">
            <table class="vehicle-table">
                <thead>
                    <tr>
                        <th>License Plate</th>
                        <th>Vehicle Details</th>
                        <th>Transmission</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="vehicleTableBody">
                    @foreach($vehicles as $vehicle)
                    <tr data-category="{{ $vehicle->category->name ?? 'None' }}" data-status="{{ $vehicle->status }}">
                        <td><span style="font-family: monospace; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-weight: 700; color: #1e293b;">{{ $vehicle->license_plate }}</span></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 8px; background: #f1f5f9; overflow: hidden; flex-shrink: 0;">
                                    @if($vehicle->images->isNotEmpty())
                                        <img src="{{ route('schools.storage.vehicle-image', ['school' => $school->slug, 'image' => $vehicle->images->first()->id]) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #cbd5e1;"><i class="bi bi-camera"></i></div>
                                    @endif
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #1e293b;">{{ $vehicle->model }}</div>
                                    <div style="font-size: 0.8rem; color: #64748b;">{{ $vehicle->category->name ?? 'Uncategorized' }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span style="padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; background: #f1f5f9; color: #475569;">{{ ucfirst($vehicle->transmission) }}</span></td>
                        <td><div style="font-weight: 500;">{{ $vehicle->branch->name ?? 'N/A' }}</div></td>
                        <td><span class="status-badge status-{{ $vehicle->status }}">{{ ucfirst(str_replace('_', ' ', $vehicle->status)) }}</span></td>
                        <td>
                            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                <button class="btn-view-details" onclick="viewVehicle({{ json_encode($vehicle->load(['category', 'branch', 'images'])) }})">
                                    <i class="bi bi-eye"></i> View Details
                                </button>

                                <button type="button" class="{{ $vehicle->status === 'active' ? 'btn-deactivate' : 'btn-activate' }}" onclick="toggleVehicleStatus({{ $vehicle->id }}, '{{ $vehicle->status }}')" title="{{ $vehicle->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi bi-{{ $vehicle->status === 'active' ? 'pause-circle' : 'play-circle' }}"></i>
                                    {{ $vehicle->status === 'active' ? 'Deactivate' : 'Activate' }}
                                </button>

                                <form id="delete-form-{{ $vehicle->id }}" action="{{ route('schools.admin.vehicles.destroy', ['school' => $school->slug, 'vehicle' => $vehicle]) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn-danger" style="height: 34px; padding: 0 12px; font-size: 0.85rem;" title="Delete" onclick="confirmDeleteVehicle({{ $vehicle->id }}, '{{ $vehicle->license_plate }}')">
                                        <i class="bi bi-trash-fill"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- Modal: Vehicle Details (Unified View/Edit) -->
<div class="modal-overlay" id="vehicleDetailModal">
    <div class="modal-content" style="width: 850px;">
        <div class="modal-header">
            <h5 id="detailModalTitle"><i class="bi bi-car-front"></i> Vehicle Information</h5>
        </div>
        <form id="editVehicleForm" method="POST" action="" enctype="multipart/form-data">
            @csrf @method('PUT')
            <input type="hidden" name="modal_type" value="edit">
            <input type="hidden" name="vehicle_id" id="editVehicleId" value="{{ old('vehicle_id') }}">
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 12px; text-transform: uppercase; display: block;">Vehicle Photos (Max 5)</label>
                        <div class="image-gallery-grid" id="imageGallery">
                            <!-- Populated by JS -->
                        </div>
                        
                        <div class="form-group edit-only">
                            <label>Add Photos</label>
                            <input type="file" name="images[]" multiple accept="image/*" style="font-size: 0.8rem;">
                        </div>
                        
                        <div class="form-group">
                            <label>Current Status</label>
                            <div class="view-only"><span id="view_v_status" class="status-badge"></span></div>
                            <select name="status" id="edit_v_status" class="edit-only" required>
                                <option value="active">Active</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="out_of_service">Out of Service</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>License Plate</label>
                                    <div class="view-only"><span id="view_v_plate" class="view-only-text"></span></div>
                                    <input type="text" name="license_plate" id="edit_v_plate" class="edit-only" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Model / Name</label>
                                    <div class="view-only"><span id="view_v_model" class="view-only-text"></span></div>
                                    <input type="text" name="model" id="edit_v_model" class="edit-only" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Category</label>
                                    <div class="view-only"><span id="view_v_cat" class="view-only-text"></span></div>
                                    <select name="category_id" id="edit_v_cat" class="edit-only" required>
                                        @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Transmission</label>
                                    <div class="view-only"><span id="view_v_trans" class="view-only-text"></span></div>
                                    <select name="transmission" id="edit_v_trans" class="edit-only" required>
                                        <option value="manual">Manual</option>
                                        <option value="automatic">Automatic</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Branch Assignment</label>
                            <div class="view-only"><span id="view_v_branch" class="view-only-text"></span></div>
                            <select name="branch_id" id="edit_v_branch" class="edit-only" required>
                                @foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Internal Notes</label>
                            <div class="view-only"><p id="view_v_notes" style="font-size: 0.9rem; color: #475569; min-height: 40px; background: #f8fafc; padding: 10px; border-radius: 8px;"></p></div>
                            <textarea name="notes" id="edit_v_notes" class="edit-only" rows="3" placeholder="Add maintenance logs or specific vehicle notes..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="view-mode-footer view-only" style="display: flex; gap: 12px; width: 100%;">
                    <button type="button" class="btn-secondary" onclick="closeVehicleModal('vehicleDetailModal')">Close</button>
                    <button type="button" class="btn-primary" style="margin-left: auto;" onclick="toggleEditMode(true)">
                        <i class="bi bi-pencil-square"></i> Edit Information
                    </button>
                </div>
                <div class="edit-mode-footer edit-only" style="display: flex; gap: 12px; width: 100%;">
                    <button type="button" class="btn-secondary" onclick="toggleEditMode(false)">Cancel</button>
                    <button type="submit" class="btn-primary" style="margin-left: auto;">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Create Vehicle -->
<div class="modal-overlay" id="createVehicleModal">
    <div class="modal-content" style="width: 700px;">
        <div class="modal-header"><h5><i class="bi bi-car-front-fill"></i> Add New Vehicle</h5></div>
        <form id="createVehicleForm" method="POST" action="{{ route('schools.admin.vehicles.store', $school) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="modal_type" value="create">
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Model / Name *</label><input type="text" name="model" value="{{ old('model') }}" placeholder="e.g. Toyota Vios 2024" required></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Plate Number *</label><input type="text" name="license_plate" value="{{ old('license_plate') }}" placeholder="ABC-1234" required></div></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Category *</label><select name="category_id" required>@foreach($categories as $cat)<option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>@endforeach</select></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Transmission *</label><select name="transmission" required><option value="manual" {{ old('transmission') == 'manual' ? 'selected' : '' }}>Manual</option><option value="automatic" {{ old('transmission') == 'automatic' ? 'selected' : '' }}>Automatic</option></select></div></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Branch *</label><select name="branch_id" required>@foreach($branches as $branch)<option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>@endforeach</select></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Status *</label><select name="status" required><option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option><option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option><option value="out_of_service" {{ old('status') == 'out_of_service' ? 'selected' : '' }}>Out of Service</option></select></div></div>
                </div>
                <div class="form-group">
                    <label>Vehicle Images (Select up to 5)</label>
                    <input type="file" name="images[]" multiple accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeVehicleModal('createVehicleModal')">Cancel</button>
                <button type="submit" class="btn-primary">Register Vehicle</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Manage Categories -->
<div class="modal-overlay" id="manageCategoriesModal">
    <div class="modal-content" style="width: 500px;">
        <div class="modal-header">
            <h5><i class="bi bi-tags"></i> Fleet Categories</h5>
        </div>
        <div class="modal-body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h6 style="margin: 0; font-weight: 700; color: #475569;">All Categories</h6>
                <button class="btn-primary" style="height: 32px; font-size: 0.8rem;" onclick="openVehicleModal('createCategoryModal')">
                    <i class="bi bi-plus"></i> New Category
                </button>
            </div>
            <table style="width: 100%; border-collapse: collapse;">
                @foreach($categories as $category)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px 0; font-weight: 600; color: #1e293b;">{{ $category->name }}</td>
                    <td style="padding: 12px 0; text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <button onclick="editCategory({{ json_encode($category) }})" style="border: none; background: #eef2ff; color: #4338ca; width: 28px; height: 28px; border-radius: 6px; cursor: pointer;">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form id="delete-cat-form-{{ $category->id }}" action="{{ route('schools.admin.vehicles.categories.destroy', ['school' => $school->slug, 'category' => $category]) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="button" style="border: none; background: #fef2f2; color: #b91c1c; width: 28px; height: 28px; border-radius: 6px; cursor: pointer;" onclick="confirmDeleteCategory({{ $category->id }}, '{{ $category->name }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeVehicleModal('manageCategoriesModal')">Close</button>
        </div>
    </div>
</div>

<!-- Modal: Create Category -->
<div class="modal-overlay" id="createCategoryModal" style="z-index: 1100;">
    <div class="modal-content" style="width: 400px;">
        <div class="modal-header"><h5><i class="bi bi-plus-circle"></i> New Category</h5></div>
        <form action="{{ route('schools.admin.vehicles.categories.store', ['school' => $school->slug]) }}" method="POST">
            @csrf
            <div class="modal-body"><div class="form-group"><label>Category Name</label><input type="text" name="name" placeholder="e.g. Luxury Sedan" required></div></div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeVehicleModal('createCategoryModal')">Cancel</button>
                <button type="submit" class="btn-primary">Create Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Category -->
<div class="modal-overlay" id="editCategoryModal" style="z-index: 1100;">
    <div class="modal-content" style="width: 400px;">
        <div class="modal-header"><h5><i class="bi bi-pencil-square"></i> Edit Category</h5></div>
        <form id="editCategoryForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-body"><div class="form-group"><label>Category Name</label><input type="text" name="name" id="edit_cat_name" required></div></div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeVehicleModal('editCategoryModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentVehicle = null;

    function openVehicleModal(id) { document.getElementById(id).classList.add('active'); document.body.style.overflow = 'hidden'; }
    function closeVehicleModal(id) { document.getElementById(id).classList.remove('active'); document.body.style.overflow = ''; }

    function viewVehicle(v) {
        currentVehicle = v;
        const modal = document.getElementById('vehicleDetailModal');
        const form = document.getElementById('editVehicleForm');
        
        // Populate form fields
        form.action = `{{ url($school->slug . '/admin/vehicles') }}/${v.id}`;
        
        // Set View mode texts
        document.getElementById('view_v_plate').innerText = v.license_plate;
        document.getElementById('view_v_model').innerText = v.model;
        document.getElementById('view_v_cat').innerText = v.category ? v.category.name : 'Uncategorized';
        document.getElementById('view_v_trans').innerText = v.transmission.charAt(0).toUpperCase() + v.transmission.slice(1);
        document.getElementById('view_v_branch').innerText = v.branch ? v.branch.name : 'N/A';
        document.getElementById('view_v_notes').innerText = v.notes || 'No internal notes recorded.';
        
        const statusBadge = document.getElementById('view_v_status');
        statusBadge.innerText = v.status.replace('_', ' ').toUpperCase();
        statusBadge.className = `status-badge status-${v.status}`;

        // Set Edit mode inputs
        document.getElementById('edit_v_plate').value = v.license_plate;
        document.getElementById('edit_v_model').value = v.model;
        document.getElementById('edit_v_cat').value = v.category_id;
        document.getElementById('edit_v_trans').value = v.transmission;
        document.getElementById('edit_v_branch').value = v.branch_id;
        document.getElementById('edit_v_status').value = v.status;
        document.getElementById('edit_v_notes').value = v.notes || '';
        document.getElementById('editVehicleId').value = v.id;
        document.getElementById('editVehicleForm').action = `{{ url($school->slug . '/admin/vehicles') }}/${v.id}`;

        // Handle Images
        renderGallery(v.images);

        toggleEditMode(false);
        openVehicleModal('vehicleDetailModal');
    }

    function renderGallery(images) {
        const gallery = document.getElementById('imageGallery');
        gallery.innerHTML = '';
        
        images.forEach(img => {
            const item = document.createElement('div');
            item.className = 'gallery-item';
            const src = `{{ url($school->slug . '/vehicle-image') }}/${img.id}`;
            item.innerHTML = `
                <img src="${src}" onclick="window.open('${src}', '_blank')">
                <button type="button" class="btn-remove-img" onclick="deleteVehicleImage(${img.id})">
                    <i class="bi bi-x"></i>
                </button>
            `;
            gallery.appendChild(item);
        });

        // Add empty slots/upload placeholder if < 5
        if (images.length < 5) {
            const placeholder = document.createElement('div');
            placeholder.className = 'upload-box edit-only';
            placeholder.innerHTML = `
                <i class="bi bi-plus-lg"></i>
                <span>${5 - images.length} More</span>
            `;
            placeholder.onclick = () => document.querySelector('#vehicleDetailModal input[type="file"]').click();
            gallery.appendChild(placeholder);
        }
    }

    async function deleteVehicleImage(imageId) {
        if (!confirm('Are you sure you want to remove this photo?')) return;
        
        try {
            const response = await fetch(`{{ url($school->slug . '/admin/vehicles') }}/${currentVehicle.id}/images/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                // Remove from local array and re-render
                currentVehicle.images = currentVehicle.images.filter(i => i.id !== imageId);
                renderGallery(currentVehicle.images);
            } else {
                alert(data.message || 'Error deleting image');
            }
        } catch (error) {
            console.error('Delete error:', error);
        }
    }

    function toggleEditMode(isEdit) {
        const modal = document.getElementById('vehicleDetailModal');
        const title = document.getElementById('detailModalTitle');
        if (isEdit) {
            modal.classList.add('edit-mode');
            modal.classList.remove('view-mode');
            title.innerHTML = '<i class="bi bi-pencil-square"></i> Edit Vehicle Details';
        } else {
            modal.classList.add('view-mode');
            modal.classList.remove('edit-mode');
            title.innerHTML = '<i class="bi bi-car-front"></i> Vehicle Information';
        }
    }

    function editCategory(cat) {
        document.getElementById('edit_cat_name').value = cat.name;
        document.getElementById('editCategoryForm').action = `{{ url($school->slug . '/admin/vehicles/categories') }}/${cat.id}`;
        openVehicleModal('editCategoryModal');
    }

    function toggleVehicleStatus(id, currentStatus) {
        const newStatus = currentStatus === 'active' ? 'out_of_service' : 'active';
        const action = currentStatus === 'active' ? 'Deactivate' : 'Activate';
        
        showConfirm({
            type: currentStatus === 'active' ? 'warning' : 'success',
            title: `${action} Vehicle`,
            message: `Are you sure you want to ${action.toLowerCase()} this vehicle?`,
            confirmText: `Yes, ${action}`,
            onConfirm: async () => {
                try {
                    const response = await fetch(`{{ url($school->slug . '/admin/vehicles') }}/${id}/toggle-status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: newStatus })
                    });
                    const data = await response.json();
                    if (data.success) {
                        if (typeof loadContent === 'function') {
                            loadContent(window.location.pathname + window.location.search);
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert(data.message || 'Error updating status');
                    }
                } catch (error) {
                    console.error('Toggle error:', error);
                    alert('Failed to update status. Please try again.');
                }
            }
        });
    }

    function confirmDeleteVehicle(id, plate) {
        showConfirm({
            type: 'danger',
            title: 'Delete Vehicle',
            message: `Are you sure you want to permanently delete vehicle "${plate}"? This action cannot be undone.`,
            confirmText: 'Yes, Delete',
            onConfirm: () => {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }

    function confirmDeleteCategory(id, name) {
        showConfirm({
            type: 'danger',
            title: 'Delete Category',
            message: `Are you sure you want to delete category "${name}"? This action cannot be undone.`,
            confirmText: 'Yes, Delete',
            onConfirm: () => {
                document.getElementById('delete-cat-form-' + id).submit();
            }
        });
    }

    function filterFleet(status, el) {
        document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
        document.querySelectorAll('#vehicleTableBody tr').forEach(r => {
            r.style.display = (status === 'all' || r.dataset.status === status) ? '' : 'none';
        });
    }

    function filterCategory(cat, el) {
        document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
        document.querySelectorAll('#vehicleTableBody tr').forEach(r => {
            r.style.display = (cat === 'all' || r.dataset.category === cat) ? '' : 'none';
        });
    }

    function filterVehicleTable() {
        const f = document.getElementById('vehicleSearch').value.toLowerCase();
        document.querySelectorAll('#vehicleTableBody tr').forEach(r => {
            const cells = r.querySelectorAll('td');
            if (cells.length < 4) return;

            const plate = cells[0].textContent.toLowerCase();
            const model = cells[1].textContent.toLowerCase();
            const branch = cells[3].textContent.toLowerCase();

            const isVisible = plate.includes(f) || model.includes(f) || branch.includes(f);
            r.style.display = isVisible ? '' : 'none';
        });
    }

    window.onclick = function(e) { if (e.target.classList.contains('modal-overlay')) closeVehicleModal(e.target.id); }

    // Modal Restoration for Validation Errors
    document.addEventListener('DOMContentLoaded', function() {
        @if($errors->any())
            @if(old('modal_type') === 'edit')
                const vehicleId = @json(old('vehicle_id'));
                const vehicle = @json($vehicles->items()).find(v => v.id == vehicleId);
                if (vehicle) {
                    viewVehicle(vehicle);
                    // Override with old values
                    document.getElementById('edit_v_plate').value = @json(old('license_plate'));
                    document.getElementById('edit_v_model').value = @json(old('model'));
                    document.getElementById('edit_v_cat').value = @json(old('category_id'));
                    document.getElementById('edit_v_trans').value = @json(old('transmission'));
                    document.getElementById('edit_v_branch').value = @json(old('branch_id'));
                    document.getElementById('edit_v_status').value = @json(old('status'));
                    document.getElementById('edit_v_notes').value = @json(old('notes'));
                }
            @else
                openVehicleModal('createVehicleModal');
            @endif
        @endif
        // Auto-replace space with dash in Plate Number
        document.querySelectorAll('input[name="license_plate"]').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/\s+/g, '-');
            });
        });
    });
</script>

@endsection
