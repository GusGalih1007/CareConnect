@extends('layout.admin.admin')
@section('title', 'List Permintaan Donasi')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="card-title mb-0">List Donasi</h1>
                <div>
                    <a href="{{ route('admin.donation.create') }}" class="btn btn-primary">
                        Buat Donasi
                    </a>
                    <a href="{{ route('admin.donation.browse') }}" class="btn btn-outline-primary">
                        Search
                    </a>
                </div>
        </div>
    </div>

    <div class="row">
        @forelse($donations as $donation)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ Str::limit($donation->title, 30) }}</h5>
                        <span
                            class="badge bg-{{ $donation->status == 'available'
                                ? 'success'
                                : ($donation->status == 'reserved'
                                    ? 'info'
                                    : ($donation->status == 'picked_up'
                                        ? 'warning'
                                        : ($donation->status == 'delivered'
                                            ? 'outline-success'
                                            : 'danger'))) }}">
                            {{ ucfirst($donation->status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">{{ Str::limit($donation->general_description, 100) }}</p>

                        <div class="mb-3">
                            <span
                                class="badge badge-{{ $donation->priority == 'urgent' ? 'danger' : ($donation->priority == 'normal' ? 'primary' : 'secondary') }}">
                                {{ ucfirst($donation->priority) }}
                            </span>
                            <span class="badge bg-info ms-2">
                                {{ $donation->items->count() }} barang
                            </span>
                        </div>

                        <div class="progress progress-bar-custom mb-3">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: {{ $donation->getProgressPercentageAttribute() }}%;">
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $donation->getFulfilledItemsAttribute() }} of {{ $donation->getTotalItemsAttribute() }}
                            barang
                            dipenuhi
                        </small>

                        <div class="mt-3">
                            <small class="d-block text-muted">

                                {{ $donation->location ? Str::limit($donation->location->address, 30) : 'Tidak ada lokasi' }}
                            </small>
                            <small class="d-block text-muted mt-1">

                                {{ $donation->created_at->format('d M Y') }}
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.donation.show', $donation->donation_request_id) }}"
                                class="btn btn-sm btn-outline-primary">
                                Lihat
                            </a>
                            @if ($donation->isPending())
                                <div>
                                    <a href="{{ route('admin.donation.edit', $donation->donation_request_id) }}"
                                        class="btn btn-sm btn-outline-warning me-1">
                                        Edit
                                    </a>
                                    <form
                                        action="{{ route('admin.donation.destroy', $donation->donation_request_id) }}"
                                        method="POST" class="d-inline" onsubmit="return confirm('Delete this request?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body py-5 text-center">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5>Tidak ada permintaan donasi</h5>
                        <p class="text-muted mb-4">Buat permintaan untuk memulai</p>
                        <a href="{{ route('admin.donation.create') }}" class="btn btn-primary">
                            Buat Permintaan Pertama
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if ($donations->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $donations->links() }}
        </div>
    @endif
@endsection
