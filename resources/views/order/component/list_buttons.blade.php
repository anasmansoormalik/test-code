@if (count($this->selected) == 0)
    <button
        class="d-flex align-items-center px-3 py-2 text-xs font-medium tracking-wider text-green-500 text-uppercase bg-white border border-green-400 gap-2 rounded-md leading-4 hover:bg-green-200 focus:outline-none uploadModal">{{ __('Upload') }}
        <i class="ti ti-upload"></i></button>
@endif
@if ($qgisActive && count($this->selected) > 0)
    @can('qgis_order')
        <button wire:click="qgisExport"
            class="d-flex align-items-center px-3 text-xs font-medium tracking-wider text-green-500 text-uppercase bg-white border border-green-400 gap-2 rounded-md leading-4 hover:bg-green-200 focus:outline-none"><img
                style="width: 92px;height:40px;" src="{{ asset('assets/img/qgis-logo.png') }}" alt="QGIS">
        </button>
    @endcan
@endif
