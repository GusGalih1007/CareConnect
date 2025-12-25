@extends('layout.admin.admin')

@section('title', 'Cari Permintaan Donasi - CareConnect')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center mb-4">
        <h3 class="card-title mb-0">Cari Permintaan Donasi</h3>
        <a href="{{ route('admin.donation-request.index') }}" class="btn btn-outline-primary">
            Permintaan Saya
        </a>
    </div>
</div>

<!-- Filter Section -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.donation-request.filter') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="category_id" class="form-label">Kategori</label>
                    <select class="form-select select2" id="category_id" name="category_id">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->category_id }}" {{ request('category_id') == $category->category_id ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="priority" class="form-label">Prioritas</label>
                    <select class="form-select" id="priority" name="priority">
                        <option value="">Semua Priorities</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="condition" class="form-label">Kondisi</label>
                    <select class="form-select" id="condition" name="condition">
                        <option value="">Semua Kondisi</option>
                        <option value="new" {{ request('condition') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="good_used" {{ request('condition') == 'good_used' ? 'selected' : '' }}>Good Used</option>
                        <option value="needs_repair" {{ request('condition') == 'needs_repair' ? 'selected' : '' }}>Need Repair</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        Filter
                    </button>
                </div>
            </div>
            
            @if(request()->hasAny(['category_id', 'priority', 'condition']))
                <div class="mt-3">
                    <a href="{{ route('admin.donation-request.browse') }}" class="btn btn-sm btn-outline-secondary">
                        Hapus Filter
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

<!-- Results -->
@if($requests->count() > 0)
    <div class="row">
        @foreach($requests as $request)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h4 class="text-white mb-4">{{ Str::limit($request->title, 25) }}</h4>
                        <h5 class="mb-4 badge bg-{{ $request->priority->value == 'urgent' ? 'danger' : ($request->priority->value == 'normal' ? 'primary' : 'secondary') }}">
                            {{ ucfirst($request->priority->value) }}
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        @if($request->general_description)
                            <h6>Deskripsi Umum</h6>
                            <p class="mb-3">{{ Str::limit($request->general_description, 80) }}</p>
                        @endif
                        
                        <div class="mb-3">
                            <small class="badge bg-info">
                                {{ $request->items->count() }} barang
                            </small>
                            <small class=" ms-3">
                                {{ $request->user->username }}
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <small class="">
                                {{ $request->location ? Str::limit($request->location->address, 30) : 'Tanpa Lokasi' }}
                            </small>
                        </div>
                        
                        <div class="progress mb-3 progress-bar-custom">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $request->getProgressPercentageAttribute() }}%;">
                            </div>
                        </div>
                        <small class=" d-block mb-3">
                            {{ $request->getProgressPercentageAttribute() }}% terpenuhi
                        </small>
                        
                        <!-- Item Preview -->
                        <div class="mb-3">
                            <small class=" d-block mb-2">Barang diperlukan:</small>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($request->items->take(3) as $item)
                                    <span class="badge bg-light text-dark border">
                                        {{ $item->category->category_name }} ({{ $item->quantity }})
                                    </span>
                                @endforeach
                                @if($request->items->count() > 3)
                                    <span class="badge bg-light text-dark border">
                                        +{{ $request->items->count() - 3 }} Lebih
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <small class=" d-block mb-2">Kondisi Barang yang Diharapkan:</small>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($request->items->take(3) as $item)
                                    <span class="badge bg-{{ $item->preferred_condition->value == 'need_repair' ? 'warning' : 'info' }} border">
                                        {{ $item->preferred_condition->value }} ({{ $item->quantity }})
                                    </span>
                                @endforeach
                                @if($request->items->count() > 3)
                                    <span class="badge bg-light text-dark border">
                                        +{{ $request->items->count() - 3 }} Lebih
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="">
                                {{ $request->created_at->diffForHumans() }}
                            </small>
                            <a href="{{ route('admin.donation-request.show', $request->donation_request_id) }}" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-eye me-1"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- Pagination -->
    @if($requests->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $requests->links() }}
        </div>
    @endif
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <h5>Permintaan Donasi Tidak Ditemukan</h5>
            <p class="">
                @if(request()->hasAny(['category_id', 'priority', 'condition']))
                    Coba perbaiki filter yang digunakan
                @else
                    Cek kembali setelah ada pemintaan baru
                @endif
            </p>
            @if(request()->hasAny(['category_id', 'priority', 'condition']))
                <a href="{{ route('admin.donation-request.browse') }}" class="btn btn-primary">
                    <i class="fas fa-times me-1"></i> Bersihkan Filter
                </a>
            @endif
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select Category',
        allowClear: true
    });
});
</script>
@endpush