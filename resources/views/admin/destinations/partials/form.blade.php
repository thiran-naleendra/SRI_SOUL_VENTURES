@php($destination = $destination ?? null)
@if($errors->any())
    <div class="alert alert-danger"><strong>Please correct the highlighted fields.</strong>
        <ul class="mb-0 mt-2">@foreach($errors->all() as $error)
        <li>{{ $error }}</li>@endforeach
        </ul>
</div>@endif

<div class="card admin-card mb-4" data-destination-section="basic">
    <div class="card-header bg-white py-3">
        <h2 class="h5 mb-0">1. Basic Information</h2>
    </div>
    <div class="card-body row">
        <div class="col-md-6"><x-admin.form-select name="destination_region_id" label="Region"
                :options="$regions->pluck('name', 'id')->all()" :selected="$destination?->destination_region_id"
                required /></div>
        <div class="col-md-6"><x-admin.form-input name="name" label="Name" :value="$destination?->name" required />
        </div>
        <div class="col-12"><x-admin.form-input name="slug" label="Slug" :value="$destination?->slug"
                help="Leave blank to generate it from the name. Duplicate slugs receive a numeric suffix." /></div>
        <div class="col-12"><x-admin.form-textarea name="short_description" label="Short description"
                :value="$destination?->short_description" rows="3" /></div>
        <div class="col-12"><x-admin.form-textarea name="full_description" label="Full description"
                :value="$destination?->full_description" rows="8" /></div>
    </div>
</div>

<div class="card admin-card mb-4" data-destination-section="cover">
    <div class="card-header bg-white py-3">
        <h2 class="h5 mb-0">2. Cover Image</h2>
    </div>
    <div class="card-body row g-3">
        <div class="col-md-7"><label class="form-label" for="cover_image">Cover image</label><input
                class="form-control @error('cover_image') is-invalid @enderror" type="file" id="cover_image"
                name="cover_image" accept="image/jpeg,image/png,image/webp"
                data-image-preview="cover-preview">@error('cover_image')
                <div class="invalid-feedback">{{ $message }}</div>@enderror @if($destination?->cover_image)
                <div class="form-check mt-3"><input type="hidden" name="remove_cover_image" value="0"><input
                        class="form-check-input" type="checkbox" value="1" id="remove_cover_image"
                        name="remove_cover_image"><label class="form-check-label text-danger"
            for="remove_cover_image">Remove current cover image</label></div>@endif
        </div>
        <div class="col-md-5"><x-admin.image-preview id="cover-preview" :src="$destination?->cover_image ? Storage::disk('public')->url($destination->cover_image) : null" alt="Cover image preview" /></div>
    </div>
</div>

@include('admin.destinations.partials.gallery')
@include('admin.destinations.partials.attractions')
@include('admin.destinations.partials.activities')
@include('admin.destinations.partials.travel-tips')

<div class="card admin-card mb-4" data-destination-section="map">
    <div class="card-header bg-white py-3">
        <h2 class="h5 mb-0">7. Map and Best Time</h2>
    </div>
    <div class="card-body row">
        <div class="col-12"><x-admin.form-input name="best_time_to_visit" label="Best time to visit"
                :value="$destination?->best_time_to_visit" /></div>
        <div class="col-md-6"><x-admin.form-input name="latitude" label="Latitude" type="number" step="0.0000001"
                min="-90" max="90" :value="$destination?->latitude" /></div>
        <div class="col-md-6"><x-admin.form-input name="longitude" label="Longitude" type="number" step="0.0000001"
                min="-180" max="180" :value="$destination?->longitude" /></div>
    </div>
</div>
<div class="card admin-card mb-4" data-destination-section="seo">
    <div class="card-header bg-white py-3">
        <h2 class="h5 mb-0">8. SEO</h2>
    </div>
    <div class="card-body"><x-admin.form-input name="meta_title" label="Meta title"
            :value="$destination?->meta_title" /><x-admin.form-textarea name="meta_description" label="Meta description"
            :value="$destination?->meta_description" rows="3" /></div>
</div>
<div class="card admin-card" data-destination-section="publishing">
    <div class="card-header bg-white py-3">
        <h2 class="h5 mb-0">9. Publishing Settings</h2>
    </div>
    <div class="card-body row">
        <div class="col-md-4"><x-admin.form-input name="display_order" label="Display order" type="number" min="0"
                :value="$destination?->display_order ?? 0" required /></div>
        <div class="col-md-8 d-flex gap-4 align-items-center">
            <div class="form-check form-switch"><input type="hidden" name="is_featured" value="0"><input
                    class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1"
                    @checked(old('is_featured', $destination?->is_featured ?? false))><label class="form-check-label"
                    for="is_featured">Featured</label></div>
            <div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input
                    class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                    @checked(old('is_active', $destination?->is_active ?? true))><label class="form-check-label"
                    for="is_active">Active</label></div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.querySelectorAll('[data-image-preview]').forEach(input => input.addEventListener('change', () => { const file = input.files[0], target = document.getElementById(input.dataset.imagePreview); if (file && target) { target.innerHTML = ''; const image = document.createElement('img'); image.src = URL.createObjectURL(file); image.alt = 'Selected image preview'; target.appendChild(image); } }));
        document.querySelectorAll('[data-repeater-add]').forEach(button => button.addEventListener('click', () => { const target = document.getElementById(button.dataset.repeaterAdd), template = document.getElementById(button.dataset.template); const index = Number(target.dataset.nextIndex || target.children.length); target.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', index)); target.dataset.nextIndex = index + 1; }));
        document.addEventListener('click', event => { const button = event.target.closest('[data-remove-new-row]'); if (button) button.closest('[data-repeater-row]').remove(); });
        document.addEventListener('click', async event => {
            const button = event.target.closest('[data-save-tip]');
            if (!button) return;
            const row = button.closest('[data-repeater-row]'), form = button.closest('form'), feedback = document.getElementById('tip-save-feedback');
            const title = row.querySelector('[name$="[title]"]'), description = row.querySelector('[name$="[description]"]'), order = row.querySelector('[name$="[display_order]"]'), id = row.querySelector('[name$="[id]"]');
            if (!title.value.trim()) { title.focus(); title.classList.add('is-invalid'); return; }
            title.classList.remove('is-invalid'); button.disabled = true; button.textContent = 'Saving…';
            const payload = new FormData();
            payload.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            payload.append('_tip_only', '1'); payload.append('editing_destination_id', form.querySelector('[name="editing_destination_id"]').value);
            if (id?.value) payload.append('tip_id', id.value);
            payload.append('tip_title', title.value); payload.append('tip_description', description.value); payload.append('tip_display_order', order.value || '0');
            try {
                const response = await fetch(form.action, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: payload });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'The travel tip could not be saved.');
                feedback.className = 'alert alert-success'; feedback.textContent = data.message; feedback.classList.remove('d-none');
                window.setTimeout(() => window.location.reload(), 500);
            } catch (error) {
                feedback.className = 'alert alert-danger'; feedback.textContent = error.message; feedback.classList.remove('d-none');
                button.disabled = false; button.textContent = 'Save this tip only';
            }
        });
        const destinationForm = document.querySelector('[data-section-save-url]');
        destinationForm?.querySelectorAll('[data-destination-section]').forEach(card => {
            const header = card.querySelector('.card-header');
            if (!header) return;
            header.classList.add('d-flex', 'justify-content-between', 'align-items-center', 'gap-3');
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-sm btn-admin-primary flex-shrink-0';
            button.textContent = 'Save this section';
            header.appendChild(button);

            button.addEventListener('click', async () => {
                const data = new FormData();
                data.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                data.append('section', card.dataset.destinationSection);
                card.querySelectorAll('input, select, textarea').forEach(field => {
                    if (!field.name || field.disabled) return;
                    if ((field.type === 'checkbox' || field.type === 'radio') && !field.checked) return;
                    if (field.type === 'file') {
                        [...field.files].forEach(file => data.append(field.name, file));
                        return;
                    }
                    data.append(field.name, field.value);
                });

                const originalText = button.textContent;
                button.disabled = true;
                button.textContent = 'Saving…';
                card.querySelector('[data-section-feedback]')?.remove();
                try {
                    const response = await fetch(destinationForm.dataset.sectionSaveUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: data,
                    });
                    const result = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const validation = result.errors ? Object.values(result.errors).flat().join(' ') : null;
                        throw new Error(validation || result.message || 'This section could not be saved.');
                    }
                    const feedback = document.createElement('div');
                    feedback.dataset.sectionFeedback = 'true';
                    feedback.className = 'alert alert-success m-3 mt-0';
                    feedback.textContent = result.message;
                    card.appendChild(feedback);
                    button.textContent = 'Saved';
                    window.setTimeout(() => { button.textContent = originalText; button.disabled = false; }, 1200);
                } catch (error) {
                    const feedback = document.createElement('div');
                    feedback.dataset.sectionFeedback = 'true';
                    feedback.className = 'alert alert-danger m-3 mt-0';
                    feedback.textContent = error.message;
                    card.appendChild(feedback);
                    button.textContent = originalText;
                    button.disabled = false;
                }
            });
        });

        document.querySelector('[data-compact-destination-form]')?.addEventListener('submit', event => {
            const form = event.currentTarget;
            if (form.dataset.compacted === 'true') return;
            const payload = {};
            const assign = (name, value) => {
                const keys = [...name.matchAll(/([^\[\]]+)/g)].map(match => match[1]);
                let current = payload;
                keys.forEach((key, index) => {
                    if (index === keys.length - 1) { current[key] = value; return; }
                    current[key] ??= {}; current = current[key];
                });
            };
            [...new FormData(form).entries()].forEach(([name, value]) => {
                if (value instanceof File || name === '_token' || name === 'editing_destination_id' || name === '_destination_payload') return;
                assign(name, value);
            });
            form.querySelectorAll('input:not([type="file"]), select, textarea').forEach(field => {
                if (!['_token', 'editing_destination_id'].includes(field.name)) field.removeAttribute('name');
            });
            const compact = document.createElement('input');
            compact.type = 'hidden'; compact.name = '_destination_payload'; compact.value = JSON.stringify(payload);
            form.appendChild(compact); form.dataset.compacted = 'true';
        });
</script>@endpush
