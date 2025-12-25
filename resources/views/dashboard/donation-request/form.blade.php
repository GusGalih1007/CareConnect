@extends('layout.admin.admin')

@section('title', isset($request) ? 'Edit Donation Request' : 'Create Donation Request')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">{{ isset($request) ? 'Edit Donation Request' : 'Create Donation Request' }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ isset($request) ? route('admin.donation-request.update', $request->donation_request_id) : route('admin.donation-request.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($request))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <!-- Title -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $request->title ?? '') }}" required>
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
                                        <option value="single_item" {{ (old('donation_type', $request->donation_type->value ?? '') == 'single_item') ? 'selected' : '' }}>Single Item</option>
                                        <option value="multiple_items" {{ (old('donation_type', $request->donation_type->value ?? '') == 'multiple_items') ? 'selected' : '' }}>Multiple Items</option>
                                    </select>
                                    @error('donation_type')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- General Description -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="general_description">General Description</label>
                                    <textarea class="form-control" id="general_description" name="general_description" rows="3">{{ old('general_description', $request->general_description ?? '') }}</textarea>
                                    @error('general_description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location_id">Location</label>
                                    <select class="form-control" id="location_id" name="location_id">
                                        <option value="">-- Select Location --</option>
                                        @foreach($userLocations as $location)
                                            <option value="{{ $location->location_id }}" {{ (old('location_id', $request->location_id ?? '') == $location->location_id) ? 'selected' : '' }}>
                                                {{ $location->address }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('location_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Priority -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="priority">Priority *</label>
                                    <select class="form-control" id="priority" name="priority" required>
                                        <option value="low" {{ (old('priority', $request->priority->value ?? '') == 'low') ? 'selected' : '' }}>Low</option>
                                        <option value="normal" {{ (old('priority', $request->priority->value ?? '') == 'normal') ? 'selected' : '' }}>Normal</option>
                                        <option value="urgent" {{ (old('priority', $request->priority->value ?? '') == 'urgent') ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Items</h5>
                                <div id="items-container">
                                    @if(isset($request) && $request->items->count() > 0)
                                        @foreach($request->items as $index => $item)
                                            <div class="item-row border p-3 mb-3">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->donation_request_item_id }}">
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
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="items[{{ $index }}][item_name]">Item Name</label>
                                                            <input type="text" class="form-control" name="items[{{ $index }}][item_name]" value="{{ old('items.'.$index.'.item_name', $item->item_name) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="items[{{ $index }}][quantity]">Quantity *</label>
                                                            <input type="number" min="1" class="form-control" name="items[{{ $index }}][quantity]" value="{{ old('items.'.$index.'.quantity', $item->quantity) }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="items[{{ $index }}][preferred_condition]">Preferred Condition *</label>
                                                            <select class="form-control" name="items[{{ $index }}][preferred_condition]" required>
                                                                <option value="new" {{ (old('items.'.$index.'.preferred_condition', $item->preferred_condition->value) == 'new') ? 'selected' : '' }}>New</option>
                                                                <option value="good_used" {{ (old('items.'.$index.'.preferred_condition', $item->preferred_condition->value) == 'good_used') ? 'selected' : '' }}>Good Used</option>
                                                                <option value="needs_repair" {{ (old('items.'.$index.'.preferred_condition', $item->preferred_condition->value) == 'needs_repair') ? 'selected' : '' }}>Needs Repair</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="items[{{ $index }}][priority]">Item Priority *</label>
                                                            <select class="form-control" name="items[{{ $index }}][priority]" required>
                                                                <option value="low" {{ (old('items.'.$index.'.priority', $item->priority->value) == 'low') ? 'selected' : '' }}>Low</option>
                                                                <option value="normal" {{ (old('items.'.$index.'.priority', $item->priority->value) == 'normal') ? 'selected' : '' }}>Normal</option>
                                                                <option value="urgent" {{ (old('items.'.$index.'.priority', $item->priority->value) == 'urgent') ? 'selected' : '' }}>Urgent</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="items[{{ $index }}][description]">Description</label>
                                                            <textarea class="form-control" name="items[{{ $index }}][description]" rows="2">{{ old('items.'.$index.'.description', $item->description) }}</textarea>
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
                                                <div class="col-md-4">
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
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="items[0][item_name]">Item Name</label>
                                                        <input type="text" class="form-control" name="items[0][item_name]">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="items[0][quantity]">Quantity *</label>
                                                        <input type="number" min="1" class="form-control" name="items[0][quantity]" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="items[0][preferred_condition]">Preferred Condition *</label>
                                                        <select class="form-control" name="items[0][preferred_condition]" required>
                                                            <option value="new">New</option>
                                                            <option value="good_used" selected>Good Used</option>
                                                            <option value="needs_repair">Needs Repair</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="items[0][priority]">Item Priority *</label>
                                                        <select class="form-control" name="items[0][priority]" required>
                                                            <option value="low">Low</option>
                                                            <option value="normal" selected>Normal</option>
                                                            <option value="urgent">Urgent</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="items[0][description]">Description</label>
                                                        <textarea class="form-control" name="items[0][description]" rows="2"></textarea>
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

                        <div class="row mt-4">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary">Submit</button>
                                <a href="{{ route('admin.donation-request.index') }}" class="btn btn-secondary">Cancel</a>
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
                    <div class="col-md-4">
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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="items[${index}][item_name]">Item Name</label>
                            <input type="text" class="form-control" name="items[${index}][item_name]">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="items[${index}][quantity]">Quantity *</label>
                            <input type="number" min="1" class="form-control" name="items[${index}][quantity]" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="items[${index}][preferred_condition]">Preferred Condition *</label>
                            <select class="form-control" name="items[${index}][preferred_condition]" required>
                                <option value="new">New</option>
                                <option value="good_used" selected>Good Used</option>
                                <option value="needs_repair">Needs Repair</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="items[${index}][priority]">Item Priority *</label>
                            <select class="form-control" name="items[${index}][priority]" required>
                                <option value="low">Low</option>
                                <option value="normal" selected>Normal</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="items[${index}][description]">Description</label>
                            <textarea class="form-control" name="items[${index}][description]" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12 text-right">
                        <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                    </div>
                </div>
            `;
            container.appendChild(newRow);
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
    });
</script>
@endsection