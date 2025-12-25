@extends('layout.admin.admin')

@section('title', isset($donation) ? 'Edit Donation' : 'Create Donation')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">{{ isset($donation) ? 'Edit Donation' : 'Create Donation' }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ isset($donation) ? route('admin.donation.update', $donation->donation_id) : route('admin.donation.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($donation))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <!-- Title -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $donation->title ?? '') }}" required>
                                    @error('title')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Donation Type -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="donation_type">Donation Type *</label>
                                    <select class="form-control" id="donation_type" name="donation_type" required>
                                        <option value="single_item" {{ (old('donation_type', $donation->donation_type ?? '') == 'single_item') ? 'selected' : '' }}>Single Item</option>
                                        <option value="multiple_items" {{ (old('donation_type', $donation->donation_type ?? '') == 'multiple_items') ? 'selected' : '' }}>Multiple Items</option>
                                    </select>
                                    @error('donation_type')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Target Request (Optional) -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="request_id">Target Donation Request (Optional)</label>
                                    <select class="form-control" id="request_id" name="request_id">
                                        <option value="">-- Donate to Specific Request --</option>
                                        @foreach($activeRequests as $request)
                                            <option value="{{ $request->donation_request_id }}" {{ (old('request_id', $donation->request_id ?? '') == $request->donation_request_id) ? 'selected' : '' }}>
                                                {{ $request->title }} ({{ $request->user->username }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('request_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status *</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="available" {{ (old('status', $donation->status ?? '') == 'available') ? 'selected' : '' }}>Available</option>
                                        <option value="reserved" {{ (old('status', $donation->status ?? '') == 'reserved') ? 'selected' : '' }}>Reserved</option>
                                        <option value="picked_up" {{ (old('status', $donation->status ?? '') == 'picked_up') ? 'selected' : '' }}>Picked Up</option>
                                        <option value="delivered" {{ (old('status', $donation->status ?? '') == 'delivered') ? 'selected' : '' }}>Delivered</option>
                                        <option value="cancelled" {{ (old('status', $donation->status ?? '') == 'cancelled') ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- General Description -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="general_description">General Description</label>
                                    <textarea class="form-control" id="general_description" name="general_description" rows="3">{{ old('general_description', $donation->general_description ?? '') }}</textarea>
                                    @error('general_description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="location_id">Pickup Location *</label>
                                    <select class="form-control" id="location_id" name="location_id" required>
                                        <option value="">-- Select Pickup Location --</option>
                                        @foreach($userLocations as $location)
                                            <option value="{{ $location->location_id }}" {{ (old('location_id', $donation->location_id ?? '') == $location->location_id) ? 'selected' : '' }}>
                                                {{ $location->address }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('location_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Donation Items</h5>
                                <div id="items-container">
                                    @if(isset($donation) && $donation->items->count() > 0)
                                        @foreach($donation->items as $index => $item)
                                            <div class="item-row border p-3 mb-3">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="items[{{ $index }}][category_id]">Category *</label>
                                                            <select class="form-control" name="items[{{ $index }}][category_id]" required>
                                                                <option value="">-- Select Category --</option>
                                                                @foreach($categories as $category)
                                                                    <option value="{{ $category->category_id }}" {{ (old('items.'.$index.'.category_id', $item->category_id) == $category->category_id) ? 'selected' : '' }}>
                                                                        {{ $category->category_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="items[{{ $index }}][item_name]">Item Name</label>
                                                            <input type="text" class="form-control" name="items[{{ $index }}][item_name]" value="{{ old('items.'.$index.'.item_name', $item->item_name) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label for="items[{{ $index }}][quantity]">Quantity *</label>
                                                            <input type="number" min="1" class="form-control" name="items[{{ $index }}][quantity]" value="{{ old('items.'.$index.'.quantity', $item->quantity) }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label for="items[{{ $index }}][condition]">Condition *</label>
                                                            <select class="form-control" name="items[{{ $index }}][condition]" required>
                                                                <option value="new" {{ (old('items.'.$index.'.condition', $item->condition) == 'new') ? 'selected' : '' }}>New</option>
                                                                <option value="good_used" {{ (old('items.'.$index.'.condition', $item->condition) == 'good_used') ? 'selected' : '' }}>Good Used</option>
                                                                <option value="needs_repair" {{ (old('items.'.$index.'.condition', $item->condition) == 'needs_repair') ? 'selected' : '' }}>Needs Repair</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label for="items[{{ $index }}][status]">Item Status *</label>
                                                            <select class="form-control" name="items[{{ $index }}][status]" required>
                                                                <option value="available" {{ (old('items.'.$index.'.status', $item->status) == 'available') ? 'selected' : '' }}>Available</option>
                                                                <option value="reserved" {{ (old('items.'.$index.'.status', $item->status) == 'reserved') ? 'selected' : '' }}>Reserved</option>
                                                                <option value="picked_up" {{ (old('items.'.$index.'.status', $item->status) == 'picked_up') ? 'selected' : '' }}>Picked Up</option>
                                                                <option value="delivered" {{ (old('items.'.$index.'.status', $item->status) == 'delivered') ? 'selected' : '' }}>Delivered</option>
                                                                <option value="cancelled" {{ (old('items.'.$index.'.status', $item->status) == 'cancelled') ? 'selected' : '' }}>Cancelled</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="items[{{ $index }}][description]">Description</label>
                                                            <textarea class="form-control" name="items[{{ $index }}][description]" rows="2">{{ old('items.'.$index.'.description', $item->description) }}</textarea>
                                                        </div>
                                                    </div>
                                                    <!-- Image Upload -->
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label>Images</label>
                                                            <div class="custom-file">
                                                                <input type="file" class="form-control" name="items[{{ $index }}][images][]" multiple accept="image/*">
                                                                <label class="custom-file-label">Choose images</label>
                                                            </div>
                                                            @if(isset($item->images) && !empty($item->images))
                                                                <div class="mt-2">
                                                                    <small>Current images:</small>
                                                                    <div class="d-flex flex-wrap">
                                                                        @foreach(json_decode($item->images, true) as $image)
                                                                            <div class="position-relative m-1">
                                                                                <img src="{{ asset('storage/' . $image) }}" alt="Item image" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                                                                <input type="hidden" name="items[{{ $index }}][existing_images][]" value="{{ $image }}">
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 text-right">
                                                        <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="item-row border p-3 mb-3">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="items[0][category_id]">Category *</label>
                                                        <select class="form-control" name="items[0][category_id]" required>
                                                            <option value="">-- Select Category --</option>
                                                            @foreach($categories as $category)
                                                                <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="items[0][item_name]">Item Name</label>
                                                        <input type="text" class="form-control" name="items[0][item_name]">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="items[0][quantity]">Quantity *</label>
                                                        <input type="number" min="1" class="form-control" name="items[0][quantity]" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="items[0][condition]">Condition *</label>
                                                        <select class="form-control" name="items[0][condition]" required>
                                                            <option value="new">New</option>
                                                            <option value="good_used" selected>Good Used</option>
                                                            <option value="needs_repair">Needs Repair</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="items[0][status]">Item Status *</label>
                                                        <select class="form-control" name="items[0][status]" required>
                                                            <option value="available" selected>Available</option>
                                                            <option value="reserved">Reserved</option>
                                                            <option value="picked_up">Picked Up</option>
                                                            <option value="delivered">Delivered</option>
                                                            <option value="cancelled">Cancelled</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="items[0][description]">Description</label>
                                                        <textarea class="form-control" name="items[0][description]" rows="2"></textarea>
                                                    </div>
                                                </div>
                                                <!-- Image Upload -->
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>Images</label>
                                                        <div class="custom-file">
                                                            <input type="file" class="form-control" name="items[0][images][]" multiple accept="image/*">
                                                            <label class="custom-file-label">Choose images</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 text-right">
                                                    <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-primary" id="add-item">Add Item</button>
                                </div>
                            </div>
                        </div>

                        {{-- <!-- Volunteer Section (Optional) -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Volunteer Information (Optional)</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="volunteer_id">Assign Volunteer</label>
                                                    <select class="form-control" id="volunteer_id" name="volunteer_id">
                                                        <option value="">-- Select Volunteer --</option>
                                                        @foreach($volunteers as $volunteer)
                                                            <option value="{{ $volunteer->user_id }}" {{ (old('volunteer_id', $donation->volunteer_id ?? '') == $volunteer->user_id) ? 'selected' : '' }}>
                                                                {{ $volunteer->username }} ({{ $volunteer->email }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="pickup_time">Scheduled Pickup Time</label>
                                                    <input type="datetime-local" class="form-control" id="pickup_time" name="pickup_time" value="{{ old('pickup_time', isset($donation->pickup_time) ? date('Y-m-d\TH:i', strtotime($donation->pickup_time)) : '') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                        <div class="row mt-4">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary">Submit</button>
                                <a href="{{ route('admin.donation.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add new item row
        document.getElementById('add-item').addEventListener('click', function() {
            const container = document.getElementById('items-container');
            const index = container.children.length;
            const newRow = document.createElement('div');
            newRow.className = 'item-row border p-3 mb-3';
            newRow.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="items[${index}][category_id]">Category *</label>
                            <select class="form-control" name="items[${index}][category_id]" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="items[${index}][item_name]">Item Name</label>
                            <input type="text" class="form-control" name="items[${index}][item_name]">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="items[${index}][quantity]">Quantity *</label>
                            <input type="number" min="1" class="form-control" name="items[${index}][quantity]" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="items[${index}][condition]">Condition *</label>
                            <select class="form-control" name="items[${index}][condition]" required>
                                <option value="new">New</option>
                                <option value="good_used" selected>Good Used</option>
                                <option value="needs_repair">Needs Repair</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="items[${index}][status]">Item Status *</label>
                            <select class="form-control" name="items[${index}][status]" required>
                                <option value="available" selected>Available</option>
                                <option value="reserved">Reserved</option>
                                <option value="picked_up">Picked Up</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="items[${index}][description]">Description</label>
                            <textarea class="form-control" name="items[${index}][description]" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Images</label>
                            <div class="custom-file">
                                <input type="file" class="form-control" name="items[${index}][images][]" multiple accept="image/*">
                                <label class="custom-file-label">Choose images</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 text-right">
                        <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                    </div>
                </div>
            `;
            container.appendChild(newRow);
            
            // Initialize file input label
            const fileInput = newRow.querySelector('.form-control');
            const fileLabel = newRow.querySelector('.custom-file-label');
            fileInput.addEventListener('change', function(e) {
                if (this.files.length > 0) {
                    fileLabel.textContent = this.files.length + ' file(s) selected';
                } else {
                    fileLabel.textContent = 'Choose images';
                }
            });
        });

        // Remove item row
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-item')) {
                const row = e.target.closest('.item-row');
                if (row) {
                    row.remove();
                }
            }
        });

        // Initialize existing file inputs
        document.querySelectorAll('.form-control').forEach(function(input) {
            const label = input.nextElementSibling;
            input.addEventListener('change', function(e) {
                if (this.files.length > 0) {
                    label.textContent = this.files.length + ' file(s) selected';
                } else {
                    label.textContent = 'Choose images';
                }
            });
        });
    });
</script>
@endsection