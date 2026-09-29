@extends('layouts.system-admin')
@section('title', 'System Logs')
@section('page-title', 'System Logs')

@section('styles')
<style>
    .logs-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .logs-header h2 {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.5rem;
        color: #1f2937;
    }
    
    .logs-header h2 i {
        color: #053d86;
    }
    
    /* Stats Cards */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    @media (max-width: 1200px) {
        .stats-row {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    .stat-box {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-left: 4px solid;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .stat-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .stat-box.critical { border-color: #dc2626; }
    .stat-box.warning { border-color: #f59e0b; }
    .stat-box.info { border-color: #3b82f6; }
    .stat-box.success { border-color: #10b981; }
    .stat-box.total { border-color: #053d86; }
    
    .stat-box .number {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.25rem;
    }
    
    .stat-box.critical .number { color: #dc2626; }
    .stat-box.warning .number { color: #f59e0b; }
    .stat-box.info .number { color: #3b82f6; }
    .stat-box.success .number { color: #10b981; }
    .stat-box.total .number { color: #053d86; }
    
    .stat-box .label {
        font-size: 0.85rem;
        color: #6b7280;
        font-weight: 500;
    }
    
    /* Filters Panel */
    .filters-panel {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    
    .filters-panel h3 {
        font-size: 1rem;
        color: #374151;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .filters-panel h3 i {
        color: #053d86;
    }
    
    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }
    
    .filter-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 0.4rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 0.6rem 0.75rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: white;
    }
    
    .filter-group select:focus,
    .filter-group input:focus {
        outline: none;
        border-color: #053d86;
        box-shadow: 0 0 0 3px rgba(5, 61, 134, 0.1);
    }
    
    .filter-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1rem;
    }
    
    .btn-filter {
        padding: 0.6rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-filter.primary {
        background: #053d86;
        color: white;
    }
    
    .btn-filter.primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(5, 61, 134, 0.4);
        background: #0a4a9e;
    }
    
    .btn-filter.secondary {
        background: #f3f4f6;
        color: #4b5563;
    }
    
    .btn-filter.secondary:hover {
        background: #e5e7eb;
    }
    
    /* Logs Table */
    .logs-table-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    
    .logs-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .logs-table thead {
        background: #f8fafc;
    }
    
    .logs-table th {
        padding: 1rem;
        text-align: left;
        font-size: 0.8rem;
        font-weight: 700;
        color: #4b5563;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .logs-table td {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.9rem;
        color: #374151;
    }
    
    .logs-table tbody tr {
        transition: background-color 0.15s;
    }
    
    .logs-table tbody tr:hover {
        background-color: #f8fafc;
    }
    
    /* Level Badges */
    .level-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .level-badge.emergency,
    .level-badge.critical {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #991b1b;
    }
    
    .level-badge.alert,
    .level-badge.error {
        background: linear-gradient(135deg, #ffedd5, #fed7aa);
        color: #9a3412;
    }
    
    .level-badge.warning {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
    }
    
    .level-badge.notice,
    .level-badge.info {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        color: #1e40af;
    }
    
    .level-badge.debug {
        background: #f3f4f6;
        color: #4b5563;
    }
    
    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-badge.resolved {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        color: #065f46;
    }
    
    .status-badge.unresolved {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #991b1b;
    }
    
    /* Category Badge */
    .category-badge {
        display: inline-block;
        padding: 0.3rem 0.6rem;
        background: #f3f4f6;
        border-radius: 6px;
        font-size: 0.8rem;
        color: #4b5563;
        font-weight: 500;
    }
    
    /* Log Message */
    .log-message {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #1f2937;
    }
    
    /* Time Display */
    .log-time {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }
    
    .log-time .date {
        font-weight: 600;
        color: #374151;
    }
    
    .log-time .time {
        font-size: 0.8rem;
        color: #6b7280;
    }
    
    /* View Button */
    .view-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 1rem;
        background: #053d86;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .view-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(5, 61, 134, 0.4);
        background: #0a4a9e;
    }
    
    /* Empty State */
    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 1rem;
    }
    
    .empty-state h3 {
        font-size: 1.25rem;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        color: #6b7280;
    }
    
    /* Pagination */
    .pagination-wrapper {
        padding: 1.5rem;
        display: flex;
        justify-content: center;
        border-top: 1px solid #f3f4f6;
    }
    
    /* School Info */
    .school-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .school-info .school-icon {
        width: 28px;
        height: 28px;
        background: #053d86;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.7rem;
        font-weight: 600;
    }
    
    .school-info .school-name {
        font-weight: 500;
    }
    
    .school-info.system .school-icon {
        background: #042e66;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .logs-table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .logs-table { min-width: 600px; }
        .filters-grid { grid-template-columns: 1fr; }
        .logs-header { flex-direction: column; gap: 10px; align-items: flex-start; }
    }
    
    @media (max-width: 480px) {
        .stats-row { grid-template-columns: 1fr; }
        .stat-box { padding: 1rem; }
    }
</style>
@endsection

@section('content')
<div class="logs-header">
    <h2>
        <i class="fas fa-clipboard-list"></i>
        System Activity Logs
    </h2>
</div>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-box critical">
        <div class="number">{{ $stats['critical'] }}</div>
        <div class="label">Critical Errors</div>
    </div>
    <div class="stat-box warning">
        <div class="number">{{ $stats['unresolved'] }}</div>
        <div class="label">Unresolved</div>
    </div>
    <div class="stat-box success">
        <div class="number">{{ $stats['today'] }}</div>
        <div class="label">Today</div>
    </div>
    <div class="stat-box info">
        <div class="number">{{ $stats['this_week'] }}</div>
        <div class="label">This Week</div>
    </div>
    <div class="stat-box total">
        <div class="number">{{ $stats['total'] }}</div>
        <div class="label">Total Logs</div>
    </div>
</div>

<!-- Filters Panel -->
<div class="filters-panel">
    <h3><i class="fas fa-filter"></i> Filter Logs</h3>
    <form method="GET">
        <div class="filters-grid">
            <div class="filter-group">
                <label>Level</label>
                <select name="level">
                    <option value="">All Levels</option>
                    <option value="emergency" {{ request('level') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                    <option value="critical" {{ request('level') === 'critical' ? 'selected' : '' }}>Critical</option>
                    <option value="alert" {{ request('level') === 'alert' ? 'selected' : '' }}>Alert</option>
                    <option value="error" {{ request('level') === 'error' ? 'selected' : '' }}>Error</option>
                    <option value="warning" {{ request('level') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="notice" {{ request('level') === 'notice' ? 'selected' : '' }}>Notice</option>
                    <option value="info" {{ request('level') === 'info' ? 'selected' : '' }}>Info</option>
                    <option value="debug" {{ request('level') === 'debug' ? 'selected' : '' }}>Debug</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Category</label>
                <select name="category">
                    <option value="">All Categories</option>
                    <option value="database" {{ request('category') === 'database' ? 'selected' : '' }}>Database</option>
                    <option value="authentication" {{ request('category') === 'authentication' ? 'selected' : '' }}>Authentication</option>
                    <option value="authorization" {{ request('category') === 'authorization' ? 'selected' : '' }}>Authorization</option>
                    <option value="validation" {{ request('category') === 'validation' ? 'selected' : '' }}>Validation</option>
                    <option value="file_upload" {{ request('category') === 'file_upload' ? 'selected' : '' }}>File Upload</option>
                    <option value="system" {{ request('category') === 'system' ? 'selected' : '' }}>System</option>
                    <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>School</label>
                <select name="school_id">
                    <option value="">All Schools</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                            {{ $school->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="filter-group">
                <label>Status</label>
                <select name="resolved">
                    <option value="">All Status</option>
                    <option value="no" {{ request('resolved') === 'no' ? 'selected' : '' }}>Unresolved</option>
                    <option value="yes" {{ request('resolved') === 'yes' ? 'selected' : '' }}>Resolved</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}">
            </div>
            
            <div class="filter-group">
                <label>To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}">
            </div>
        </div>
        
        <div class="filter-actions">
            <button type="submit" class="btn-filter primary">
                <i class="fas fa-search"></i> Apply Filters
            </button>
            <a href="{{ route('system-admin.logs') }}" class="btn-filter secondary">
                <i class="fas fa-times"></i> Clear
            </a>
        </div>
    </form>
</div>

<!-- Logs Table -->
<div class="logs-table-wrapper">
    @if($logs->count() > 0)
        <table class="logs-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Level</th>
                    <th>Category</th>
                    <th>School</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>
                            <div class="log-time">
                                <span class="date">{{ $log->created_at->timezone('Asia/Manila')->format('M d, Y') }}</span>
                                <span class="time">{{ $log->created_at->timezone('Asia/Manila')->format('h:i A') }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="level-badge {{ $log->level }}">
                                @switch($log->level)
                                    @case('emergency')
                                    @case('critical')
                                        <i class="fas fa-exclamation-circle"></i>
                                        @break
                                    @case('alert')
                                    @case('error')
                                        <i class="fas fa-times-circle"></i>
                                        @break
                                    @case('warning')
                                        <i class="fas fa-exclamation-triangle"></i>
                                        @break
                                    @case('notice')
                                    @case('info')
                                        <i class="fas fa-info-circle"></i>
                                        @break
                                    @default
                                        <i class="fas fa-bug"></i>
                                @endswitch
                                {{ ucfirst($log->level) }}
                            </span>
                        </td>
                        <td>
                            <span class="category-badge">{{ ucfirst(str_replace('_', ' ', $log->category)) }}</span>
                        </td>
                        <td>
                            @if($log->school)
                                <div class="school-info">
                                    <div class="school-icon">{{ strtoupper(substr($log->school->name, 0, 2)) }}</div>
                                    <span class="school-name">{{ $log->school->name }}</span>
                                </div>
                            @else
                                <div class="school-info system">
                                    <div class="school-icon"><i class="fas fa-cog"></i></div>
                                    <span class="school-name">System</span>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="log-message" title="{{ $log->message }}">
                                {{ $log->message }}
                            </div>
                        </td>
                        <td>
                            @if($log->resolved_at)
                                <span class="status-badge resolved">
                                    <i class="fas fa-check-circle"></i> Resolved
                                </span>
                            @else
                                <span class="status-badge unresolved">
                                    <i class="fas fa-clock"></i> Open
                                </span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('system-admin.logs.show', $log) }}" class="view-btn">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="pagination-wrapper">
            {{ $logs->appends(request()->query())->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-clipboard-check"></i>
            <h3>No logs found</h3>
            <p>No system logs match your current filters. Try adjusting your search criteria.</p>
        </div>
    @endif
</div>
@endsection
