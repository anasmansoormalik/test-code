<div>
    <form wire:submit="submit">
        <div class="modal-body">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="text-center mb-4">
                <h3 class="mb-2" id="popupTitle">{{ __('order.uploadDocumentsTitle') }}</h3>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label for="project">{{ __('order.project') }}</label>
                    <select wire:model.live="uproject" class="form-control">
                        <option></option>
                        @foreach (App\Models\Project::all() as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('uproject')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label for="type">{{ __('order.uploadType') }}</label>
                    <select wire:model.live="utype" class="form-control">
                        <option></option>
                        <option value="excel">{{ __('order.excel') }}</option>
                        <option value="empty_pdf">{{ __('order.emptyPdf') }}</option>
                        <option value="final_pdf">{{ __('order.finalPdf') }}</option>
                    </select>
                    @error('utype')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            @if (count($c_rules) > 0)
                <livewire:custom-dropzone wire:model="uploadFiles" :rules="$c_rules" :multiple="true" :excel="$utype == 'excel'"
                    :project="$uproject" :key="serialize($c_rules)" />
            @endif
            @error('uploadFiles')
                <span class="text-danger">{{ $message }}</span>
            @enderror



            <div class="col-12 text-center mt-1">
                <button type="button" wire:click="resetc" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                    aria-label="Close">{{ __('common.cancel') }}</button>
                <button class="btn btn-success">{{ __('common.submit') }}</button>
            </div>
        </div>
    </form>
</div>
