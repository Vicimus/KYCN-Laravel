@extends('layouts.admin')

@section('admin')
    <div class="container">
        <div class="d-flex flex-column bg-white rounded-2 shadow-sm w-100">
            <div class="p-3">
                <h3 class="m-0">View Dealerships</h3>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>Dealership Name</th>
                        <th>Logo</th>
                        <th>URL</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($dealers as $d)
                        <tr>
                            <td><a href="{{ route('admin.dealers.show', $d) }}">{{ $d->name }}</a></td>
                            <td>@if($d->dealership_logo)
                                    <img src="{{ $d->dealership_logo }}" style="height:34px">
                                @endif</td>
                            <td class="text-break">
                                @php $url = url('/?d='.$d->code); @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ $url }}" target="_blank">{{ $url }}</a>
                                    <button class="btn btn-sm btn-outline-secondary"
                                            onclick="navigator.clipboard.writeText('{{ $url }}')">Copy
                                    </button>
                                </div>
                            </td>
                            <td>{{ $d->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $d->updated_at?->format('Y-m-d H:i:s') }}</td>
                            <td>
                                <a href="{{ route('admin.dealers.edit', $d) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
