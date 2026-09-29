@extends('layouts.system-admin')
@section('title', 'Courses')
@section('page-title', 'All Courses')
@section('content')
<div class="card">
    <div class="card-header"><h3>Courses ({{ $courses->total() }})</h3></div>
    <div class="card-body">
        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>School</th>
                    <th>Duration</th>
                    <th>Price</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                <tr>
                    <td><strong>{{ $course->title }}</strong></td>
                    <td>{{ $course->school->name }}</td>
                    <td>{{ $course->duration_hours ?? 'N/A' }} hours</td>
                    <td>₱{{ number_format($course->price ?? 0, 2) }}</td>
                    <td>{{ $course->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        {{ $courses->links() }}
    </div>
</div>
@endsection
