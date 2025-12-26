@extends('layout.admin.admin')
@section('title', $data->title)
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ $data->title }}</h1>
                {{-- <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.donation-request.index') }}">My Requests</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View</li>
                </ol>
            </nav> --}}
            </div>
            <div class="d-flex">
                <a href="{{ route('admin.donation-validation.index') }}" class="btn btn-outline-secondary me-2">
                    Kembali
                </a>
                @if ($data->isActive())
                    <a href="{{ route('admin.donation.browse') }}?category={{ $data->items->first()->category_id ?? '' }}"
                        class="btn btn-success">
                        Cari Donasi
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Request Details Card -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h4 class="mb-4">Detail Permintaan</h4>
                    <div class="mb-4">
                        <span
                            class="badge bg-{{ $data->status->value == 'pending' ? 'warning' : ($data->status->value == 'active' ? 'success' : ($data->status->value == 'fulfilled' ? 'info' : 'secondary')) }}">
                            {{ ucfirst($data->status->value) }}
                        </span>
                        <span
                            class="badge bg-{{ $data->priority->value == 'urgent' ? 'danger' : ($data->priority->value == 'normal' ? 'primary' : 'secondary') }} ms-2">
                            {{ ucfirst($data->priority->value) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if ($data->general_description)
                        <div class="mb-4">
                            <h6>Deskripsi</h6>
                            <p class="text-muted">{{ $data->general_description }}</p>
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Location</h6>
                            <p class="text-muted">
                                {{ $data->location ? $data->location->address : 'Tidak ada alamat' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Tipe Permintaan</h6>
                            <p class="text-muted">
                                {{ $data->donation_type == 'single_item' ? 'Single Item' : 'Multiple Items' }}
                            </p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6>Progress</h6>
                        <div class="progress progress-bar-custom mb-2" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: {{ $data->getProgressPercentageAttribute() }}%;">
                                {{ $data->getProgressPercentageAttribute() }}%
                            </div>
                        </div>
                        <p class="text-muted mb-0">
                            {{ $data->getFulfilledItemsAttribute() }} of {{ $data->getTotalItemsAttribute() }} Barang
                            Terpenuhi
                        </p>
                    </div>

                    @if ($data->validation)
                        <div
                            class="alert alert-{{ $data->validation->status->value == 'approved' ? 'success' : ($data->validation->status->value == 'rejected' ? 'danger' : 'warning') }} mb-4">
                            <h6>Status Persetujuan: {{ ucfirst($data->validation->status->value) }}</h6>
                            @if ($data->validation->note)
                                <p class="mb-0"><strong>Catatan:</strong> {{ $data->validation->note }}</p>
                            @endif
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Permintaaan Dibuat Oleh:</h6>
                            <p class="text-muted">
                                {{ $data->user->username }}
                            </p>
                        </div>
                        @if ($data->validation->admin)
                            <div class="col-md-6">
                                <h6>Disetujui Oleh</h6>
                                <p class="text-muted">
                                    {{ $data->validation->user->username }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <small class="text-muted">
                        Dibuat: {{ $data->created_at->format('d M Y, H:i') }} |
                        Diubah: {{ $data->updated_at->format('d M Y, H:i') }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <!-- Potential Matches Card -->
            {{-- <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h4 class="mb-4">Potensi Donatur</h4>
            </div>
            <div class="card-body">
                @if ($potentialMatches && $potentialMatches->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach ($potentialMatches->take(5) as $match)
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">{{ $match['donation_item']->item_name }}</h6>
                                        <small class="text-muted">
                                            Tersedia: {{ $match['available_quantity'] }} | 
                                            Butuh: {{ $match['needed_quantity'] }}
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
                                    Lihat Donasi
                                </a>
                            </div>
                            @if (!$loop->last)<hr class="my-2">@endif
                        @endforeach
                    </div>
                    
                    @if ($potentialMatches->count() > 5)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.donation.browse') }}?request_id={{ $data->donation_request_id }}" 
                               class="btn btn-sm btn-outline-primary">
                                Lihat Semua {{ $potentialMatches->count() }} Donatur
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">Rekomendasi donatur belum ditemukan</p>
                        <p class="text-muted small">Kami akan memberitahukan bila sudah ditemukan</p>
                    </div>
                @endif
            </div> --}}

            <!-- Quick Actions Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h4 class="mb-4">Quick Actions</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if ($data->isPending())
                            <button data-bs-toggle="modal" data-bs-target="#validateDonation" data-status="approved"
                                class="btn btn-success">
                                Setujui
                            </button>
                            <button data-bs-toggle="modal" data-bs-target="#validateDonation" data-status="need_revision"
                                class="btn btn-info">
                                Perlu Koreksi
                            </button>
                            <button data-bs-toggle="modal" data-bs-target="#validateDonation" data-status="rejected"
                                class="btn btn-danger">
                                Tolak
                            </button>

                            <div class="modal fade" id="validateDonation" tabindex="-1"
                                aria-labelledby="validateDonationLabel" aria-hidden="true" data-bs-backdrop="static"
                                data-bs-keyboard="false">
                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="validateDonationLabel">Validasi Donasi</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form
                                            action="{{ route('admin.donation-validation.validate', $data->donation_request_id) }}"
                                            method="POST">
                                            <div class="modal-body">
                                                {{ csrf_field() }}
                                                <!-- Input hidden untuk status -->
                                                <input type="hidden" id="donationStatus" name="status"
                                                    value="">

                                                <div class="form-group">
                                                    <label for="note" class="form-label">Catata Admin:</label>
                                                    <textarea rows="10" id="note" class="form-control" name="note"></textarea>
                                                </div>

                                                <!-- Konten lainnya di dalam modal -->
                                                <p>Apakah Anda yakin ingin memproses donasi ini?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Tutup</button>
                                                <button type="submit" class="btn btn-primary">Kirim</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <a href="{{ route('admin.donation.browse') }}" class="btn btn-outline-primary">
                            Telusuri Donasi
                        </a>

                        <a href="{{ route('admin.donation-match.for-request', $data->donation_request_id) }}"
                            class="btn btn-outline-info">
                            Lihat Rekomendasi
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <!-- Request Items Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h4 class="mb-4">Barang yang Diminta ({{ $data->items->count() }})</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table-hover table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Barang</th>
                                    <th>Kategori</th>
                                    <th>Jumlah</th>
                                    <th>Kondisi</th>
                                    <th>Prioritas</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data->items as $item)
                                    <tr>
                                        <td><img src="https://placehold.co/50" width="50"></td>
                                        <td>
                                            <strong>{{ $item->item_name ?? 'Unnamed Item' }}</strong>
                                            @if ($item->description)
                                                <br><small
                                                    class="text-muted">{{ Str::limit($item->description, 50) }}</small>
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
                                            <span
                                                class="badge bg-{{ $item->priority->value == 'urgent' ? 'danger' : ($item->priority->value == 'normal' ? 'primary' : 'secondary') }}">
                                                {{ ucfirst($item->priority->value) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $item->status->value == 'pending' ? 'warning' : ($item->status->value == 'partially_fulfilled' ? 'info' : 'success') }}">
                                                {{ ucfirst(str_replace('_', ' ', $item->status->value)) }}
                                            </span>
                                        </td>
                                        <td width="150">
                                            <div class="progress progress-bar-custom mb-1">
                                                <div class="progress-bar bg-success" role="progressbar"
                                                    style="width: {{ $item->quantity > 0 ? round(($item->fulfilled_quantity / $item->quantity) * 100) : 0 }}%;">
                                                </div>
                                            </div>
                                            <small
                                                class="text-muted">{{ $item->fulfilled_quantity }}/{{ $item->quantity }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        // Event listener untuk tombol-tombol yang mengaktifkan modal
        $('button[data-bs-toggle="modal"]').on('click', function() {
            // Ambil nilai data-status dari tombol yang diklik (approved/rejected)
            var status = $(this).attr('data-status');

            // Set nilai ke input hidden di dalam modal
            $('#donationStatus').val(status);
        });
    </script>
@endsection
