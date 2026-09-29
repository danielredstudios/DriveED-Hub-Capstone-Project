@extends('layouts.system-admin')
@section('title', 'Instructors')
@section('page-title', 'All Instructors')
@section('content')
<div class="filters">
    <form method="GET" class="filter-grid">
        <div class="form-group">
            <label>School</label>
            <select name="school_id" class="form-control">
                <option value="">All Schools</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="">All</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="form-group">
            <label>Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email" class="form-control">
        </div>
        <div class="form-group form-group-actions">
            <button type="submit" class="btn btn-primary btn-full-width">Filter</button>
        </div>
    </form>
</div>
<div class="card">
    <div class="card-header"><h3>Instructors ({{ $instructors->total() }})</h3></div>
    <div class="card-body">
        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>School</th>
                    <th>Status</th>
                    <th>Availability</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($instructors as $instructor)
                <tr>
                    <td><strong>{{ $instructor->name }}</strong></td>
                    <td>{{ $instructor->email }}</td>
                    <td>{{ $instructor->school->name }}</td>
                    <td><span class="badge {{ $instructor->status === 'active' ? 'badge-success' : 'badge-secondary' }}">{{ ucfirst($instructor->status) }}</span></td>
                    <td><span class="badge {{ $instructor->availability === 'available' ? 'badge-info' : 'badge-danger' }}">{{ ucfirst($instructor->availability) }}</span></td>
                    <td>{{ $instructor->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        {{ $instructors->appends(request()->query())->links() }}
    </div>
</div>
@endsection
