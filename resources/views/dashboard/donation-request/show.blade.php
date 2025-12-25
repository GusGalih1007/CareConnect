@extends('layout.admin.admin')
@section('title', $request->title)
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $request->title }}</h1>
            {{-- <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.donation-request.index') }}">My Requests</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View</li>
                </ol>
            </nav> --}}
        </div>
        <div class="d-flex">
            <a href="{{ route('admin.donation-request.index') }}" class="btn btn-outline-secondary me-2">
                Back
            </a>
            @if($request->isPending())
                <a href="{{ route('admin.donation-request.edit', $request->donation_request_id) }}" class="btn btn-warning me-2">
                    Edit
                </a>
            @endif
            @if($request->isActive())
                <a href="{{ route('admin.donation.browse') }}?category={{ $request->items->first()->category_id ?? '' }}" class="btn btn-success">
                    Find Donations
                </a>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Request Details Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h4 class="mb-4">Request Details</h4>
                <div class="mb-4">
                    <span class="badge bg-{{ $request->status->value == 'pending' ? 'warning' : ($request->status->value == 'active' ? 'success' : ($request->status->value == 'fulfilled' ? 'info' : 'secondary')) }}">
                        {{ ucfirst($request->status->value) }}
                    </span>
                    <span class="badge bg-{{ $request->priority->value == 'urgent' ? 'danger' : ($request->priority->value == 'normal' ? 'primary' : 'secondary') }} ms-2">
                        {{ ucfirst($request->priority->value) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                @if($request->general_description)
                    <div class="mb-4">
                        <h6>Description</h6>
                        <p class="text-muted">{{ $request->general_description }}</p>
                    </div>
                @endif
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Location</h6>
                        <p class="text-muted">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            {{ $request->location ? $request->location->address : 'No location specified' }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6>Request Type</h6>
                        <p class="text-muted">
                            <i class="fas fa-{{ $request->donation_type == 'single_item' ? 'box' : 'boxes' }} text-primary me-2"></i>
                            {{ $request->donation_type == 'single_item' ? 'Single Item' : 'Multiple Items' }}
                        </p>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6>Progress</h6>
                    <div class="progress mb-2 progress-bar-custom" style="height: 20px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $request->getProgressPercentageAttribute() }}%;">
                            {{ $request->getProgressPercentageAttribute() }}%
                        </div>
                    </div>
                    <p class="text-muted mb-0">
                        {{ $request->getFulfilledItemsAttribute() }} of {{ $request->getTotalItemsAttribute() }} items fulfilled
                    </p>
                </div>
                
                @if($request->validation)
                    <div class="mb-4 alert alert-{{ $request->validation->status->value == 'approved' ? 'success' : ($request->validation->status->value == 'rejected' ? 'danger' : 'warning') }}">
                        <h6>Validation Status: {{ ucfirst($request->validation->status->value) }}</h6>
                        @if($request->validation->note)
                            <p class="mb-0"><strong>Note:</strong> {{ $request->validation->note }}</p>
                        @endif
                    </div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Permintaaan Dibuat Oleh:</h6>
                        <p class="text-muted">
                            {{ $request->user->username }}
                        </p>
                    </div>
                    @if ($request->validation->admin)
                    <div class="col-md-6">
                        <h6>Disetujui Oleh</h6>
                        <p class="text-muted">
                            {{ $request->validation->user->username }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <small class="text-muted">
                    Created: {{ $request->created_at->format('d M Y, H:i') }} | 
                    Updated: {{ $request->updated_at->format('d M Y, H:i') }}
                </small>
            </div>
        </div>
        
        <!-- Request Items Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h4 class="mb-4">Request Items ({{ $request->items->count() }})</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Condition</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($request->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->item_name ?? 'Unnamed Item' }}</strong>
                                        @if($item->description)
                                            <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->category->category_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ ucfirst(str_replace('_', ' ', $item->preferred_condition->value)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $item->priority->value == 'urgent' ? 'danger' : ($item->priority->value == 'normal' ? 'primary' : 'secondary') }}">
                                            {{ ucfirst($item->priority->value) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $item->status->value == 'pending' ? 'warning' : ($item->status->value == 'partially_fulfilled' ? 'info' : 'success') }}">
                                            {{ ucfirst(str_replace('_', ' ', $item->status->value)) }}
                                        </span>
                                    </td>
                                    <td width="150">
                                        <div class="progress mb-1 progress-bar-custom">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: {{ $item->quantity > 0 ? round(($item->fulfilled_quantity / $item->quantity) * 100) : 0 }}%;">
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $item->fulfilled_quantity }}/{{ $item->quantity }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Potential Matches Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h4 class="mb-4">Potential Matches</h4>
            </div>
            <div class="card-body">
                @if($potentialMatches && $potentialMatches->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($potentialMatches->take(5) as $match)
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">{{ $match['donation_item']->item_name }}</h6>
                                        <small class="text-muted">
                                            Available: {{ $match['available_quantity'] }} | 
                                            Needed: {{ $match['needed_quantity'] }}
                                        </small>
                                    </div>
                                    <span class="badge bg-warning">Score: {{ $match['score'] }}</span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        {{ $match['donation_item']->donation->user->username }}
                                    </small>
                                    <small class="text-muted">
                                        {{ round($match['distance'], 1) }} km
                                    </small>
                                </div>
                                
                                <a href="{{ route('admin.donation.show', $match['donation_item']->donation->donation_id) }}" 
                                   class="btn btn-sm btn-outline-primary w-100">
                                    <i class="fas fa-external-link-alt me-1"></i> View Donation
                                </a>
                            </div>
                            @if(!$loop->last)<hr class="my-2">@endif
                        @endforeach
                    </div>
                    
                    @if($potentialMatches->count() > 5)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.donation.browse') }}?request_id={{ $request->donation_request_id }}" 
                               class="btn btn-sm btn-outline-primary">
                                View All {{ $potentialMatches->count() }} Matches
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-search fa-2x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No potential matches found yet</p>
                        <p class="text-muted small">We'll notify you when matches are found</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Quick Actions Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h4 class="mb-4">Quick Actions</h4>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($request->isPending())
                        <a href="{{ route('admin.donation-request.edit', $request->donation_request_id) }}" class="btn btn-warning">
                            Edit Request
                        </a>
                    @endif
                    
                    <a href="{{ route('admin.donation.browse') }}" class="btn btn-outline-primary">
                        Browse Donations
                    </a>
                    
                    <a href="{{ route('admin.donation-match.for-request', $request->donation_request_id) }}" class="btn btn-outline-info">
                        View Matches
                    </a>
                    
                    @if($request->isPending())
                        <form class="d-grid" action="{{ route('admin.donation-request.destroy', $request->donation_request_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this request?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                Delete Request
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection