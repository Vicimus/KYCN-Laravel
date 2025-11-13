@php
    $isEdit = ($mode ?? null) === 'edit';
    $title = $isEdit ? 'Edit Dealership' : 'Create Dealership';
    $action = $isEdit ? route('admin.dealers.update', $dealer) : route('admin.dealers.store');
@endphp

@extends('layouts.app')
@section('title', $title)

@section('content')
    <div class="d-flex flex-column bg-white rounded-2 shadow-sm w-100">
        <div class="p-3">
            <div class="fw-bold">{{ $title }}</div>
        </div>

        <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="m-0 px-3 pb-3">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="row g-3 mb-3 align-items-start">
                <div class="col-md-3 col-lg-2">
                    <label class="fs-md d-block mb-1">Current Logo</label>
                    <div class="logo-uploader">
                        <div class="position-relative d-inline-block outer-logo">
                            @include('partials.dealer-logo', ['dealer' => $dealer, 'large' => true])

                            <div class="logo-actions btn-group" role="group">
                                <label for="logo_file"
                                       class="btn btn-sm btn-primary"
                                >
                                    <i class="fas fa-cloud-arrow-up"></i>
                                </label>
                                <button type="button"
                                        class="btn btn-sm btn-danger {{ (bool) old('dealership_logo', $dealer->logo_url ?? null) ? '' : 'd-none' }}"
                                        id="clearLogoBtn"
                                >
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </div>
                        </div>

                        <input type="file"
                               class="visually-hidden"
                               id="logo_file"
                               name="logo_file"
                               accept="image/*">
                        <input type="hidden" name="remove_logo" id="removeLogo" value="0">

                        <div class="fs-sm text-secondary mt-2">PNG/JPG/WebP/SVG, up to 2&nbsp;MB.</div>
                        @error('logo_file')
                        <div class="text-danger fs-md mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="col-md-9 col-lg-10">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="dealership_name" class="fs-md">Dealership Name</label>
                            <input name="name"
                                   id="dealership_name"
                                   class="form-control form-control-sm"
                                   value="{{ old('name', $isEdit ? $dealer->name : '') }}"
                                   autocomplete="off"
                                   required>
                            @error('name')
                            <div class="text-danger fs-md">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-7">
                            <label for="logo_upload_src" class="fs-md">
                                Logo URL <span class="text-secondary">(optional)</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-link"></i></span>
                                <input name="dealership_logo"
                                       type="url"
                                       id="logo_upload_src"
                                       class="form-control form-control-sm"
                                       placeholder="https://..."
                                       value="{{ old('dealership_logo', $dealer->dealership_logo_url) }}"/>

                                <button class="btn btn-sm btn-outline-secondary"
                                        type="button"
                                        id="previewUrlBtn"
                                        title="Preview URL"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('dealership_logo')
                            <div class="text-danger fs-md">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label for="kyc_date" class="fs-md">Know Your Car Night Date</label>
                            <input type="date"
                                   class="form-control form-control-sm @error('know_your_car_date') is-invalid @enderror"
                                   id="kyc_date"
                                   name="know_your_car_date"
                                   value="{{ old('know_your_car_date', optional($dealer->know_your_car_date)->toDateString()) }}"
                                   min="{{ now()->toDateString() }}">
                            @error('know_your_car_date')
                            <div class="text-danger fs-md">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-2">
                <a href="{{ route('admin.dealers.index') }}" class="btn btn-sm btn-secondary">Back to Dealers</a>
                <button class="btn btn-sm btn-primary">{{ $isEdit ? 'Save Changes' : 'Create Dealership' }}</button>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fileInput = document.getElementById('logo_file');
            const urlInput = document.getElementById('logo_upload_src');
            const previewBtn = document.getElementById('previewUrlBtn');
            const clearBtn = document.getElementById('clearLogoBtn');
            const removeFlag = document.getElementById('removeLogo');
            const frame = document.querySelector('.logo-uploader .logo-thumb');

            function hasPreviewImage() {
                return !!(frame && frame.querySelector('img'));
            }
            
            function updatePreviewState() {
                previewBtn.disabled = urlInput.value.trim() === '';
            }

            function updateClearVisibility() {
                const show = hasPreviewImage()
                    || (urlInput && urlInput.value.trim().length > 0)
                    || (fileInput && fileInput.files && fileInput.files.length > 0);
                if (clearBtn) {
                    clearBtn.classList.toggle('d-none', !show);
                }
            }

            function swapToImage(src) {
                if (!frame) {
                    return;
                }

                const initials = frame.querySelector('.logo-initials');
                if (initials) {
                    initials.remove();
                }

                let img = frame.querySelector('img');
                if (!img) {
                    img = document.createElement('img');
                    img.alt = 'Logo preview';
                    img.decoding = 'async';
                    img.loading = 'lazy';
                    frame.appendChild(img);
                    frame.classList.remove('initials');
                    frame.classList.add('normal');
                }
                img.src = src;
                updateClearVisibility();
            }

            function swapToInitials(text) {
                if (!frame) {
                    return;
                }

                const img = frame.querySelector('img');
                if (img) {
                    img.remove();
                }

                if (!frame.querySelector('.logo-initials')) {
                    const div = document.createElement('div');
                    div.className = 'logo-initials';
                    div.setAttribute('aria-hidden', 'true');
                    div.textContent = text || '—';
                    frame.appendChild(div);
                }
                frame.classList.remove('normal');
                frame.classList.add('initials');
                updateClearVisibility();
            }

            fileInput?.addEventListener('change', () => {
                const f = fileInput.files && fileInput.files[0];
                if (!f) {
                    return;
                }
                swapToImage(URL.createObjectURL(f));
            });

            clearBtn?.addEventListener('click', () => {
                if (removeFlag) {
                    removeFlag.value = '1';
                }
                if (fileInput) {
                    fileInput.value = '';
                }
                if (urlInput) {
                    urlInput.value = '';
                }
                swapToInitials(@json($dealer->initials ?? '—'));
            });

            previewBtn?.addEventListener('click', () => {
                const v = (urlInput?.value || '').trim();
                console.log('v: ', v);
                if (v) {
                    swapToImage(v);
                }
            });

            urlInput?.addEventListener('input', () => {
                updateClearVisibility();
                updatePreviewState();
            });

            updateClearVisibility();
            updatePreviewState();
        });
    </script>
@endsection
