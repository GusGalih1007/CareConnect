@extends('layout.admin.admin')

@section('title', isset($request) ? 'Edit Permintaan Donasi' : 'Buat Permintaan Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        {{ isset($request) ? 'Edit Permintaan Donasi' : 'Buat Permintaan Baru' }}
                    </h4>
                    <a href="{{ route('admin.donation-request.index') }}" class="btn btn-outline-secondary">
                        Kembali
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <form id="donationRequestForm" method="POST" 
                      action="{{ isset($request) ? route('admin.donation-request.update', $request->donation_request_id) : route('admin.donation-request.store') }}">
                    @csrf
                    @if(isset($request))
                        @method('PUT')
                    @endif

                    <!-- Basic Information -->
                    <div class="mb-4">
                        <h5 class="mb-3 border-bottom pb-2">
                            Informasi Permintaan
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label">Judul Permintaan *</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" 
                                       value="{{ old('title', $request->title ?? '') }}" 
                                       placeholder="Contoh: Pakaian Untuk Anak Yatim Piatu" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="priority" class="form-label">Prioritas *</label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" name="priority" required>
                                    <option value="low" {{ old('priority', $request->priority ?? 'normal') == 'low' ? 'selected' : '' }}>Rendah</option>
                                    <option value="normal" {{ old('priority', $request->priority ?? 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="urgent" {{ old('priority', $request->priority ?? 'normal') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="general_description" class="form-label">Deskripsi Donasi</label>
                            <textarea class="form-control @error('general_description') is-invalid @enderror" 
                                      id="general_description" name="general_description" 
                                      rows="3" placeholder="Deskripsikan Kebutuhanmu...">{{ old('general_description', $request->general_description ?? '') }}</textarea>
                            @error('general_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="location_id" class="form-label">Lokasi *</label>
                                <select class="form-select select2 @error('location_id') is-invalid @enderror" 
                                        id="location_id" name="location_id" required>
                                    <option value="">Pilih Lokasi</option>
                                    @foreach($userLocations as $location)
                                        <option value="{{ $location->location_id }}"
                                            {{ old('location_id', $request->location_id ?? '') == $location->location_id ? 'selected' : '' }}>
                                            {{ $location->address }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <a href="{{ route('admin.profile') }}" target="_blank" class="text-decoration-none">
                                        Tambah Lokasi Baru
                                    </a>
                                </small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="donation_type" class="form-label">Jenis Permintaan *</label>
                                <select class="form-select @error('donation_type') is-invalid @enderror" 
                                        id="donation_type" name="donation_type" required>
                                    <option value="single_item" {{ old('donation_type', $request->donation_type ?? 'single_item') == 'single_item' ? 'selected' : '' }}>Single Item</option>
                                    <option value="multiple_items" {{ old('donation_type', $request->donation_type ?? '') == 'multiple_items' ? 'selected' : '' }}>Multiple Items</option>
                                </select>
                                @error('donation_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Request Items -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <h5 class="mb-0">
                                Barang Yang Diminta
                            </h5>
                            <button type="button" id="addItemBtn" class="btn btn-sm btn-success">
                                Tambah Barang
                            </button>
                        </div>
                        
                        <p class="text-muted mb-4">
Tambahkan barang yang Anda butuhkan. Anda dapat menambahkan beberapa barang dalam satu donasi.</p>
                        
                        <div id="itemsContainer">
                            @php
                                $oldItems = old('items', []);
                                $itemCount = count($oldItems);
                                
                                if ($itemCount == 0 && isset($request) && $request->items->count() > 0) {
                                    $itemCount = $request->items->count();
                                }
                                
                                if ($itemCount == 0) {
                                    $itemCount = 1;
                                }
                            @endphp
                            
                            @for($i = 0; $i < $itemCount; $i++)
                                <div class="item-card card mb-3" data-index="{{ $i }}">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-4">Barang #<span class="item-number">{{ $i + 1 }}</span></h6>
                                        @if($i > 0)
                                            <button type="button" class="btn btn-sm btn-danger remove-item-btn">
                                                Hilangkan
                                            </button>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        @if(isset($request) && isset($request->items[$i]))
                                            <input type="hidden" name="items[{{ $i }}][id]" value="{{ $request->items[$i]->donation_request_item_id }}">
                                        @endif
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Kategori *</label>
                                                <select name="items[{{ $i }}][category_id]" class="form-select category-select" required>
                                                    <option value="">Select Category</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->category_id }}"
                                                            {{ (old('items.'.$i.'.category_id') == $category->category_id || 
                                                                (isset($request) && isset($request->items[$i]) && $request->items[$i]->category_id == $category->category_id)) ? 'selected' : '' }}>
                                                            {{ $category->category_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('items.'.$i.'.category_id')
                                                    <div class="text-danger small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nama Barang</label>
                                                <input type="text" name="items[{{ $i }}][item_name]" class="form-control" 
                                                       value="{{ old('items.'.$i.'.item_name', isset($request) && isset($request->items[$i]) ? $request->items[$i]->item_name : '') }}"
                                                       placeholder="Contoh: Baju Anak Ukuran M">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="items[{{ $i }}][description]" class="form-control" rows="2"
                                                      placeholder="Deskripsikan barang ini...">{{ old('items.'.$i.'.description', isset($request) && isset($request->items[$i]) ? $request->items[$i]->description : '') }}</textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Quantity *</label>
                                                <input type="number" name="items[{{ $i }}][quantity]" class="form-control" min="1" required
                                                       value="{{ old('items.'.$i.'.quantity', isset($request) && isset($request->items[$i]) ? $request->items[$i]->quantity : 1) }}">
                                            </div>
                                            
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Condition *</label>
                                                <select name="items[{{ $i }}][preferred_condition]" class="form-select" required>
                                                    <option value="new" {{ (old('items.'.$i.'.preferred_condition') == 'new' || 
                                                        (isset($request) && isset($request->items[$i]) && $request->items[$i]->preferred_condition == 'new')) ? 'selected' : '' }}>
                                                        Baru
                                                    </option>
                                                    <option value="good_used" {{ (old('items.'.$i.'.preferred_condition') == 'good_used' || 
                                                        (isset($request) && isset($request->items[$i]) && $request->items[$i]->preferred_condition == 'good_used')) ? 'selected' : '' }}>
                                                        Baik
                                                    </option>
                                                    <option value="needs_repair" {{ (old('items.'.$i.'.preferred_condition') == 'needs_repair' || 
                                                        (isset($request) && isset($request->items[$i]) && $request->items[$i]->preferred_condition == 'needs_repair')) ? 'selected' : '' }}>
                                                        Perlu Perbaikan
                                                    </option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Item Priority *</label>
                                                <select name="items[{{ $i }}][priority]" class="form-select" required>
                                                    <option value="low" {{ (old('items.'.$i.'.priority') == 'low' || 
                                                        (isset($request) && isset($request->items[$i]) && $request->items[$i]->priority == 'low')) ? 'selected' : '' }}>
                                                        Rendah
                                                    </option>
                                                    <option value="normal" {{ (old('items.'.$i.'.priority') == 'normal' || 
                                                        (isset($request) && isset($request->items[$i]) && $request->items[$i]->priority == 'normal')) ? 'selected' : '' }}>
                                                        Normal
                                                    </option>
                                                    <option value="urgent" {{ (old('items.'.$i.'.priority') == 'urgent' || 
                                                        (isset($request) && isset($request->items[$i]) && $request->items[$i]->priority == 'urgent')) ? 'selected' : '' }}>
                                                        Urgent
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('admin.donation-request.index') }}" class="btn btn-outline-secondary">
                            Batalkan
                        </a>
                        <button type="submit" class="btn btn-primary">
                            {{ isset($request) ? 'Ubah Permintaan' : 'Buat Permintaan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsContainer = document.getElementById('itemsContainer');
    const addItemBtn = document.getElementById('addItemBtn');
    let itemCount = {{ $itemCount }};
    
    // Template untuk item baru
    const itemTemplate = (index) => `
        <div class="item-card card mb-3" data-index="${index}">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-4">Item #<span class="item-number">${index + 1}</span></h6>
                <button type="button" class="btn btn-sm btn-danger remove-item-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category *</label>
                        <select name="items[${index}][category_id]" class="form-select category-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Item Name</label>
                        <input type="text" name="items[${index}][item_name]" class="form-control" 
                               placeholder="e.g., Children's Clothes Size M">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="items[${index}][description]" class="form-control" rows="2"
                              placeholder="Describe this item..."></textarea>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="items[${index}][quantity]" class="form-control" min="1" required value="1">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Condition *</label>
                        <select name="items[${index}][preferred_condition]" class="form-select" required>
                            <option value="new">New</option>
                            <option value="good_used" selected>Good Used</option>
                            <option value="needs_repair">Needs Repair</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Item Priority *</label>
                        <select name="items[${index}][priority]" class="form-select" required>
                            <option value="low">Low</option>
                            <option value="normal" selected>Normal</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>`;
    
    // Add new item
    addItemBtn.addEventListener('click', function() {
        const newIndex = itemCount;
        itemsContainer.insertAdjacentHTML('beforeend', itemTemplate(newIndex));
        itemCount++;
        updateItemNumbers();
        initializeSelect2();
    });
    
    // Remove item
    itemsContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item-btn')) {
            const itemCard = e.target.closest('.item-card');
            if (document.querySelectorAll('.item-card').length > 1) {
                itemCard.remove();
                updateItemNumbers();
            } else {
                alert('At least one item is required');
            }
        }
    });
    
    // Update item numbers
    function updateItemNumbers() {
        const itemCards = itemsContainer.querySelectorAll('.item-card');
        itemCards.forEach((card, index) => {
            const itemNumber = card.querySelector('.item-number');
            itemNumber.textContent = index + 1;
            card.setAttribute('data-index', index);
            
            // Update input names
            const inputs = card.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/items\[\d+\]/, `items[${index}]`);
                    input.setAttribute('name', newName);
                }
            });
        });
    }
    
    // Initialize Select2 for new selects
    function initializeSelect2() {
        $('.category-select:not(.select2-hidden-accessible)').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select Category',
            allowClear: true
        });
    }
    
    // Initialize existing selects
    initializeSelect2();
    
    // Form validation
    const form = document.getElementById('donationRequestForm');
    form.addEventListener('submit', function(e) {
        const itemCards = itemsContainer.querySelectorAll('.item-card');
        if (itemCards.length === 0) {
            e.preventDefault();
            alert('Please add at least one item');
            return false;
        }
        
        // Validate each item
        let isValid = true;
        itemCards.forEach(card => {
            const category = card.querySelector('[name*="[category_id]"]');
            const quantity = card.querySelector('[name*="[quantity]"]');
            
            if (!category.value) {
                isValid = false;
                category.classList.add('is-invalid');
            }
            
            if (!quantity.value || parseInt(quantity.value) < 1) {
                isValid = false;
                quantity.classList.add('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields for each item');
        }
    });
});
</script>
@endpush

@push('styles')
<style>
    .item-card {
        transition: all 0.3s ease;
    }
    .item-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .remove-item-btn {
        transition: all 0.2s ease;
    }
    .remove-item-btn:hover {
        transform: scale(1.1);
    }
</style>
@endpush