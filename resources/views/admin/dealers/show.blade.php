@extends('layouts.app')

@section('title', 'Dealer Details')

@section('content')
    <div class="d-flex flex-column bg-white rounded-2 shadow-sm w-100 overflow-hidden">
        <div class="d-flex justify-content-between p-3 gap-3">
            <div class="d-flex align-items-center gap-3">
                @if($dealer->dealership_logo)
                    <img src="{{ $dealer->dealership_logo }}" class="table-dealer-logo" alt="{{ $dealer->name }} Logo"/>
                @endif
                <div class="fw-bold">{{ $dealer->name }}</div>
            </div>

            <div class="btn-group" role="group">
                <a class="btn btn-sm btn-outline-primary"
                   href="{{ route('admin.dealers.export', ['dealer' => $dealer->portal_token]) }}"
                   title="Export CSV"
                >
                    <i class="fas fa-file-lines"></i>
                </a>
                <a class="btn btn-sm btn-outline-primary"
                   href="{{ route('admin.dealers.ics', ['dealer' => $dealer->portal_token]) }}"
                   title="Add to Calendar"
                >
                    <i class="fas fa-calendar-plus"></i>
                </a>
            </div>
        </div>

        @if($rows->count() > 0)
            <div class="table-responsive border-top">
                <table class="table kycn-table sm-table m-0 text-nowrap">
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
                    @foreach($rows as $r)
                        <tr>
                            <td><strong>{{ $r->created_at?->format('M jS, Y • g:ia') }}</strong></td>
                            <td>{{ $r->full_name }}</td>
                            <td>{{ (int) $r->guest_count }}</td>
                            <td>{{ $r->wants_appointment ? 'Yes' : 'No' }}</td>
                            <td>{!! nl2br(e((string) $r->notes)) !!}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-3 pb-3">
                @include('components.alert', ['heading' => 'No submissions yet.', 'type' => 'info'])
            </div>
        @endif
    </div>
@endsection
