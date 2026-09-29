@extends('layouts.system-admin')
@section('title', 'Schedules')
@section('page-title', 'All Schedules')
@section('content')
<div class="card">
    <div class="card-header"><h3>Schedules ({{ $bookings->total() }})</h3></div>
    <div class="card-body">
        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>School</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $booking)
                <tr>
                    <td>{{ $booking->id }}</td>
                    <td>{{ $booking->student->name ?? 'N/A' }}</td>
                    <td>{{ $booking->course->title ?? 'N/A' }}</td>
                    <td>{{ $booking->school->name }}</td>
                    <td>
                        <span class="badge 
                            @if($booking->status === 'completed') badge-success
                            @elseif($booking->status === 'pending') badge-warning
                            @else badge-secondary
                            @endif">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </td>
                    <td>{{ $booking->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        {{ $bookings->links() }}
    </div>
</div>
@endsection
