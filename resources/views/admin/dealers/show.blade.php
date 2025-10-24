@extends('layouts.admin')

@section('admin')
    <div class="container-fluid py-3">
        <div class="d-flex align-items-center gap-3 mb-3">
            @if($dealer->dealership_logo)
                <img src="{{ $dealer->dealership_logo }}" style="height:32px">
            @endif
            <h4 class="m-0">{{ $dealer->name }}</h4>
        </div>

        <div class="table-responsive">
            <table class="table kycn-table sm-table m-0">
                <thead>
                <tr>
                    <th>When</th>
                    <th>Name</th>
                    <th>Guests</th>
                    <th>Appt?</th>
                    <th>Notes</th>
                </tr>
                </thead>
                <tbody>
                @forelse($rows as $r)
                    <tr>
                        <td>{{ $r->created_at?->format('M jS, Y • g:ia') }}</td>
                        <td>{{ $r->full_name }}</td>
                        <td>{{ (int) $r->guest_count }}</td>
                        <td>{{ $r->wants_appointment ? 'Yes' : 'No' }}</td>
                        <td>{!! nl2br(e((string)$r->notes)) !!}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-secondary">No submissions yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
