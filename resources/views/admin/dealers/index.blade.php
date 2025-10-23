@extends('layouts.admin')

@section('admin')
    <div class="container">
        <div class="d-flex flex-column bg-white rounded-2 shadow-sm w-100">
            <div class="p-3">
                <h3 class="m-0">View Dealerships</h3>
            </div>

            <div class="p-3">
                <form method="get" class="d-flex align-items-end gap-2">
                    <input type="search" class="form-control form-control-sm"
                           name="q" value="{{ request('q') }}" placeholder="Search dealers...">
                    <button type="reset" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-rotate-left"></i>
                    </button>
                </form>

                @if(!empty($q))
                    <div class="text-secondary fs-md">
                        Showing {{ $dealers->total() }} result(s) for “{{ $q }}”
                    </div>
                @endif
            </div>

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
                                    <a href="{{ $url }}">{{ $url }}</a>
                                    <button class="btn btn-sm btn-outline-secondary"
                                            onclick="navigator.clipboard.writeText('{{ $url }}')">Copy
                                    </button>
                                </div>
                            </td>
                            <td>{{ $d->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $d->updated_at?->format('Y-m-d H:i:s') }}</td>
                            <td>
                                <a href="{{ route('admin.dealers.edit', $d) }}"
                                   class="btn btn-sm btn-outline-secondary">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-3 pb-3">
                {{ $dealers->links() }}
            </div>
        </div>
    </div>
@endsection
