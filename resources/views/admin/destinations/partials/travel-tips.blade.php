<div class="card admin-card mb-4" data-destination-section="travel_tips">
        <div class="card-header bg-white py-3 d-flex justify-content-between">
                <h2 class="h5 mb-0">6. Travel Tips</h2><button type="button" class="btn btn-sm btn-outline-success"
                        data-repeater-add="tip-rows" data-template="tip-template">Add travel tip</button>
        </div>
        <div class="card-body">
                <div id="tip-save-feedback" class="alert d-none" role="status"></div>
                <div id="tip-rows" data-next-index="{{ $destination?->travelTips->count() ?? 0 }}">
                        @foreach($destination?->travelTips ?? [] as $index => $tip)
                                <div class="border rounded p-3 mb-3" data-repeater-row><input type="hidden"
                                                name="travel_tips[{{ $index }}][id]" value="{{ $tip->id }}">
                                        <div class="row g-3">
                                                <div class="col-md-4"><label class="form-label">Title</label><input
                                                                class="form-control" name="travel_tips[{{ $index }}][title]"
                                                                value="{{ $tip->title }}"></div>
                                                <div class="col-md-6"><label class="form-label">Description</label><textarea
                                                                class="form-control"
                                                                name="travel_tips[{{ $index }}][description]">{{ $tip->description }}</textarea>
                                                </div>
                                                <div class="col-md-1"><label class="form-label">Order</label><input
                                                                class="form-control" type="number" min="0"
                                                                name="travel_tips[{{ $index }}][display_order]"
                                                                value="{{ $tip->display_order }}"></div>
                                                <div class="col-md-1 form-check align-self-end mb-2"><input
                                                                class="form-check-input" type="checkbox"
                                                                name="travel_tips[{{ $index }}][_remove]" value="1"
                                                                id="remove-tip-{{ $tip->id }}"><label
                                                                class="form-check-label text-danger"
                                                                for="remove-tip-{{ $tip->id }}">Remove</label></div>
                                        </div>
                                        <div class="mt-3"><button type="button" class="btn btn-sm btn-outline-success"
                                                        data-save-tip>Save this
                                                        tip only</button></div>
                        </div>@endforeach
                </div>
        </div>
</div>
<template id="tip-template">
        <div class="border rounded p-3 mb-3" data-repeater-row>
                <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Title</label><input class="form-control"
                                        name="travel_tips[__INDEX__][title]" required></div>
                        <div class="col-md-6"><label class="form-label">Description</label><textarea
                                        class="form-control" name="travel_tips[__INDEX__][description]"></textarea>
                        </div>
                        <div class="col-md-1"><label class="form-label">Order</label><input class="form-control"
                                        type="number" min="0" name="travel_tips[__INDEX__][display_order]" value="0">
                        </div>
                        <div class="col-md-1 d-flex align-items-end"><button type="button"
                                        class="btn btn-outline-danger" data-remove-new-row>×</button></div>
                </div>
                <div class="mt-3"><button type="button" class="btn btn-sm btn-outline-success" data-save-tip>Save this
                                tip
                                only</button></div>
        </div>
</template>
