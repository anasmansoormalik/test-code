@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts.layoutMaster')

@section('vendor-style')
    @livewireStyles
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /*! tailwindcss v3.4.0 | MIT License | https://tailwindcss.com*/
        *,
        :after,
        :before {
            box-sizing: border-box;
            border: 0 solid #e5e7eb
        }

        :after,
        :before {
            --tw-content: ""
        }

        :host,
        html {
            line-height: 1.5;
            -webkit-text-size-adjust: 100%;
            -moz-tab-size: 4;
            -o-tab-size: 4;
            tab-size: 4;
            font-family: ui-sans-serif, system-ui, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
            font-feature-settings: normal;
            font-variation-settings: normal;
            -webkit-tap-highlight-color: transparent
        }

        body {
            margin: 0;
            line-height: inherit
        }

        hr {
            height: 0;
            color: inherit;
            border-top-width: 1px
        }

        abbr:where([title]) {
            -webkit-text-decoration: underline dotted;
            text-decoration: underline dotted
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-size: inherit;
            font-weight: inherit
        }

        a {
            color: inherit;
            text-decoration: inherit
        }

        b,
        strong {
            font-weight: bolder
        }

        code,
        kbd,
        pre,
        samp {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, Liberation Mono, Courier New, monospace;
            font-feature-settings: normal;
            font-variation-settings: normal;
            font-size: 1em
        }

        small {
            font-size: 80%
        }

        sub,
        sup {
            font-size: 75%;
            line-height: 0;
            position: relative;
            vertical-align: initial
        }

        sub {
            bottom: -.25em
        }

        sup {
            top: -.5em
        }

        table {
            text-indent: 0;
            border-color: inherit;
            border-collapse: collapse
        }

        button,
        input,
        optgroup,
        select,
        textarea {
            font-family: inherit;
            font-feature-settings: inherit;
            font-variation-settings: inherit;
            font-size: 100%;
            font-weight: inherit;
            line-height: inherit;
            color: inherit;
            margin: 0;
            padding: 0
        }

        button,
        select {
            text-transform: none
        }

        :-moz-focusring {
            outline: auto
        }

        :-moz-ui-invalid {
            box-shadow: none
        }

        progress {
            vertical-align: initial
        }

        ::-webkit-inner-spin-button,
        ::-webkit-outer-spin-button {
            height: auto
        }

        [type=search] {
            -webkit-appearance: textfield;
            outline-offset: -2px
        }

        ::-webkit-search-decoration {
            -webkit-appearance: none
        }

        ::-webkit-file-upload-button {
            -webkit-appearance: button;
            font: inherit
        }

        summary {
            display: list-item
        }

        blockquote,
        dd,
        dl,
        figure,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        hr,
        p,
        pre {
            margin: 0
        }

        fieldset {
            margin: 0
        }

        fieldset,
        legend {
            padding: 0
        }

        dialog {
            padding: 0
        }

        textarea {
            resize: vertical
        }

        input::-moz-placeholder,
        textarea::-moz-placeholder {
            opacity: 1;
            color: #9ca3af
        }

        input::placeholder,
        textarea::placeholder {
            opacity: 1;
            color: #9ca3af
        }

        [role=button],
        button {
            cursor: pointer
        }

        :disabled {
            cursor: default
        }

        audio,
        canvas,
        embed,
        iframe,
        img,
        object,
        svg,
        video {
            display: block;
            vertical-align: middle
        }

        img,
        video {
            max-width: 100%;
            height: auto
        }

        [hidden] {
            display: none
        }

        *,
        ::backdrop,
        :after,
        :before {
            --tw-border-spacing-x: 0;
            --tw-border-spacing-y: 0;
            --tw-translate-x: 0;
            --tw-translate-y: 0;
            --tw-rotate: 0;
            --tw-skew-x: 0;
            --tw-skew-y: 0;
            --tw-scale-x: 1;
            --tw-scale-y: 1;
            --tw-pan-x: ;
            --tw-pan-y: ;
            --tw-pinch-zoom: ;
            --tw-scroll-snap-strictness: proximity;
            --tw-gradient-from-position: ;
            --tw-gradient-via-position: ;
            --tw-gradient-to-position: ;
            --tw-ordinal: ;
            --tw-slashed-zero: ;
            --tw-numeric-figure: ;
            --tw-numeric-spacing: ;
            --tw-numeric-fraction: ;
            --tw-ring-inset: ;
            --tw-ring-offset-width: 0px;
            --tw-ring-offset-color: #fff;
            --tw-ring-color: #3b82f680;
            --tw-ring-offset-shadow: 0 0 #0000;
            --tw-ring-shadow: 0 0 #0000;
            --tw-shadow: 0 0 #0000;
            --tw-shadow-colored: 0 0 #0000;
            --tw-blur: ;
            --tw-brightness: ;
            --tw-contrast: ;
            --tw-grayscale: ;
            --tw-hue-rotate: ;
            --tw-invert: ;
            --tw-saturate: ;
            --tw-sepia: ;
            --tw-drop-shadow: ;
            --tw-backdrop-blur: ;
            --tw-backdrop-brightness: ;
            --tw-backdrop-contrast: ;
            --tw-backdrop-grayscale: ;
            --tw-backdrop-hue-rotate: ;
            --tw-backdrop-invert: ;
            --tw-backdrop-opacity: ;
            --tw-backdrop-saturate: ;
            --tw-backdrop-sepia:
        }

        .dz-sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0
        }

        .dz-mb-2 {
            margin-bottom: .5rem
        }

        .dz-mb-4 {
            margin-bottom: 1rem
        }

        .dz-mr-3 {
            margin-right: .75rem
        }

        .dz-mt-2 {
            margin-top: .5rem
        }

        .dz-mt-5 {
            margin-top: 1.25rem
        }

        .dz-flex {
            display: flex
        }

        .dz-hidden {
            display: none
        }

        .dz-h-14 {
            height: 3.5rem
        }

        .dz-h-5 {
            height: 1.25rem
        }

        .dz-h-6 {
            height: 1.5rem
        }

        .dz-h-8 {
            height: 2rem
        }

        .dz-h-auto {
            height: auto
        }

        .dz-h-full {
            height: 100%
        }

        .dz-w-14 {
            width: 3.5rem
        }

        .dz-w-5 {
            width: 1.25rem
        }

        .dz-w-6 {
            width: 1.5rem
        }

        .dz-w-8 {
            width: 2rem
        }

        .dz-w-full {
            width: 100%
        }

        .dz-max-w-2xl {
            max-width: 42rem
        }

        .dz-flex-none {
            flex: none
        }

        @keyframes dz-spin {
            to {
                transform: rotate(1turn)
            }
        }

        .dz-animate-spin {
            animation: dz-spin 1s linear infinite
        }

        .dz-cursor-pointer {
            cursor: pointer
        }

        .dz-flex-col {
            flex-direction: column
        }

        .dz-flex-wrap {
            flex-wrap: wrap
        }

        .dz-items-start {
            align-items: flex-start
        }

        .dz-items-center {
            align-items: center
        }

        .dz-justify-start {
            justify-content: flex-start
        }

        .dz-justify-center {
            justify-content: center
        }

        .dz-justify-between {
            justify-content: space-between
        }

        .dz-gap-1 {
            gap: .25rem
        }

        .dz-gap-2 {
            gap: .5rem
        }

        .dz-gap-3 {
            gap: .75rem
        }

        .dz-gap-x-10 {
            -moz-column-gap: 2.5rem;
            column-gap: 2.5rem
        }

        .dz-gap-y-2 {
            row-gap: .5rem
        }

        .dz-overflow-hidden {
            overflow: hidden
        }

        .dz-rounded {
            border-radius: .25rem
        }

        .dz-border {
            border-width: 1px
        }

        .dz-border-dashed {
            border-style: dashed
        }

        .dz-border-gray-200 {
            --tw-border-opacity: 1;
            border-color: rgb(229 231 235/var(--tw-border-opacity))
        }

        .dz-border-gray-500 {
            --tw-border-opacity: 1;
            border-color: rgb(107 114 128/var(--tw-border-opacity))
        }

        .dz-bg-gray-100 {
            --tw-bg-opacity: 1;
            background-color: rgb(243 244 246/var(--tw-bg-opacity))
        }

        .dz-bg-gray-50 {
            --tw-bg-opacity: 1;
            background-color: rgb(249 250 251/var(--tw-bg-opacity))
        }

        .dz-bg-red-50 {
            --tw-bg-opacity: 1;
            background-color: rgb(254 242 242/var(--tw-bg-opacity))
        }

        .dz-bg-white {
            --tw-bg-opacity: 1;
            background-color: rgb(255 255 255/var(--tw-bg-opacity))
        }

        .dz-fill-blue-600 {
            fill: #2563eb
        }

        .dz-object-fill {
            -o-object-fit: fill;
            object-fit: fill
        }

        .dz-p-10 {
            padding: 2.5rem
        }

        .dz-p-4 {
            padding: 1rem
        }

        .dz-py-8 {
            padding-top: 2rem;
            padding-bottom: 2rem
        }

        .dz-text-center {
            text-align: center
        }

        .dz-text-lg {
            font-size: 1.125rem;
            line-height: 1.75rem
        }

        .dz-text-sm {
            font-size: .875rem;
            line-height: 1.25rem
        }

        .dz-font-medium {
            font-weight: 500
        }

        .dz-font-semibold {
            font-weight: 600
        }

        .dz-text-black {
            --tw-text-opacity: 1;
            color: rgb(0 0 0/var(--tw-text-opacity))
        }

        .dz-text-gray-200 {
            --tw-text-opacity: 1;
            color: rgb(229 231 235/var(--tw-text-opacity))
        }

        .dz-text-gray-500 {
            --tw-text-opacity: 1;
            color: rgb(107 114 128/var(--tw-text-opacity))
        }

        .dz-text-gray-600 {
            --tw-text-opacity: 1;
            color: rgb(75 85 99/var(--tw-text-opacity))
        }

        .dz-text-red-400 {
            --tw-text-opacity: 1;
            color: rgb(248 113 113/var(--tw-text-opacity))
        }

        .dz-text-red-500 {
            --tw-text-opacity: 1;
            color: rgb(239 68 68/var(--tw-text-opacity))
        }

        .dz-text-red-800 {
            --tw-text-opacity: 1;
            color: rgb(153 27 27/var(--tw-text-opacity))
        }

        .dz-text-slate-900 {
            --tw-text-opacity: 1;
            color: rgb(15 23 42/var(--tw-text-opacity))
        }

        .dz-antialiased {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale
        }

        @media (prefers-color-scheme:dark) {
            .dark\:dz-border-gray-600 {
                --tw-border-opacity: 1;
                border-color: rgb(75 85 99/var(--tw-border-opacity))
            }

            .dark\:dz-border-gray-700 {
                --tw-border-opacity: 1;
                border-color: rgb(55 65 81/var(--tw-border-opacity))
            }

            .dark\:dz-bg-gray-700 {
                --tw-bg-opacity: 1;
                background-color: rgb(55 65 81/var(--tw-bg-opacity))
            }

            .dark\:dz-bg-gray-800 {
                --tw-bg-opacity: 1;
                background-color: rgb(31 41 55/var(--tw-bg-opacity))
            }

            .dark\:dz-bg-red-600 {
                --tw-bg-opacity: 1;
                background-color: rgb(220 38 38/var(--tw-bg-opacity))
            }

            .dark\:dz-text-gray-400 {
                --tw-text-opacity: 1;
                color: rgb(156 163 175/var(--tw-text-opacity))
            }

            .dark\:dz-text-gray-700 {
                --tw-text-opacity: 1;
                color: rgb(55 65 81/var(--tw-text-opacity))
            }

            .dark\:dz-text-red-100 {
                --tw-text-opacity: 1;
                color: rgb(254 226 226/var(--tw-text-opacity))
            }

            .dark\:dz-text-red-200 {
                --tw-text-opacity: 1;
                color: rgb(254 202 202/var(--tw-text-opacity))
            }

            .dark\:dz-text-slate-100 {
                --tw-text-opacity: 1;
                color: rgb(241 245 249/var(--tw-text-opacity))
            }

            .dark\:dz-text-white {
                --tw-text-opacity: 1;
                color: rgb(255 255 255/var(--tw-text-opacity))
            }

            .dark\:hover\:dz-border-gray-500:hover {
                --tw-border-opacity: 1;
                border-color: rgb(107 114 128/var(--tw-border-opacity))
            }
        }
    </style>
    <style>
        .alert-list {
            list-style: square;

            padding-inline-start: 40px;
            text-align: left;
        }
    </style>
    <style>
        .applyBtn {
            background-color: #7367f0 !important;
        }

        .select2-results__option {

            white-space: nowrap;

        }

        .select2-container--open {
            width: max-content !important;
        }
    </style>
@endsection

@section('vendor-script')
    @livewireScripts
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}"></script>

    <!-- Laravel Javascript Validation -->
    <script type="text/javascript" src="{{ url('vendor/jsvalidation/js/jsvalidation.js') }}"></script>

@endsection

@section('page-script')

    <script>
        $(document).on('click', '.uploadModal', function() {
            $('#uploadModal').modal('show');
        });
        window.addEventListener('file-uploaded', function(e) {
            $('#uploadModal').modal('hide');
            Swal.fire({
                title: '{{ __('alert.SuccessTitle') }}',
                html: e.detail[0].success,
                icon: 'success',
                customClass: {
                    confirmButton: 'btn btn-success'
                },
                buttonsStyling: false
            });
        });
        window.addEventListener('file-upload-error', function(e) {
            $('#uploadModal').modal('hide');
            Swal.fire({
                title: '{{ __('alert.errorTitle') }}',
                html: e.detail[0].success,
                icon: 'error',
                customClass: {
                    confirmButton: 'btn btn-danger'
                },
                buttonsStyling: false
            });
        });
    </script>

@endsection

@section('title', __('order.listPageTitle'))

@section('content')
    <livewire:orders />
    <!-- Order Uploads Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple order-upload-modal">
            <div class="modal-content p-3 p-md-5">
                @livewire('OrderUploads')
            </div>
        </div>
    </div>
    <!--/ Order Uploads Modal -->
@endsection
