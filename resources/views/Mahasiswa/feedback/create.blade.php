@extends('Mahasiswa.layout_mahasiswa')

@section('title', 'Isi Kuesioner Feedback')

@push('styles')
{{-- Custom styles for this page --}}
<style>
    .card-header {
        background: linear-gradient(90deg, #0ea5e9 0%, #0284c7 100%);
    }
    .list-group-item {
        border-bottom: 1px solid #eee !important;
    }
    .list-group-item:last-child {
        border-bottom: none !important;
    }
    .btn-check:checked+.btn-outline-primary {
        background-color: #0ea5e9;
        border-color: #0ea5e9;
        color: white;
        box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.25);
    }
    .btn-outline-primary {
        min-width: 40px;
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-header text-white p-4">
                    <h3 class="fw-bold mb-2 d-block text-dark">Formulir Feedback Perkuliahan</h3>
                    <p class="fw-bold mb-2 d-block text-dark small">Berikan penilaian Anda untuk sesi perkuliahan ini.</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    {{-- Detail Sesi Perkuliahan --}}
                    <div class="p-3 rounded mb-4" style="background-color: #f0f9ff;">
                        <h5 class="text-primary">Detail Sesi</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Mata Kuliah:</strong> {{ $pertemuan->nama_matkul }}</p>
                                <p class="mb-1"><strong>Dosen:</strong> {{ $pertemuan->nama_dosen }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Pertemuan Ke:</strong> {{ $pertemuan->pertemuan_ke }}</p>
                                <p class="mb-1"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($pertemuan->tanggal)->format('d F Y') }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">Terdapat Kesalahan!</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Form Kuesioner --}}
                    <form action="{{ route('mahasiswa.feedback.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_pertemuan" value="{{ $pertemuan->id_pertemuan }}">

                        <h5 class="mb-3 text-dark">Pertanyaan Kuesioner</h5>

                        <ul class="list-group list-group-flush">
                            @foreach ($pertanyaan as $index => $p)
                                <li class="list-group-item px-0 py-4">
                                    <label class="fw-bold mb-2 d-block text-dark">
                                        {{ $index + 1 }}. {{ $p->teks_pertanyaan }}
                                    </label>

                                    @if ($p->jenis_pertanyaan == 'skala')
                                        {{-- Untuk pertanyaan tipe skala --}}
                                        <div class="d-flex flex-wrap justify-content-center justify-content-md-start mt-3" style="gap: 0.5rem;">
                                            @foreach ($skala as $s)
                                                <div>
                                                    <input class="btn-check" type="radio" name="jawaban[{{ $p->id_pertanyaan }}][skala]" id="skala_{{ $p->id_pertanyaan }}_{{ $s->nilai }}" value="{{ $s->nilai }}" autocomplete="off" required>
                                                    <label class="btn btn-outline-primary" for="skala_{{ $p->id_pertanyaan }}_{{ $s->nilai }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $s->keterangan }}">
                                                        {{ $s->nilai }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <small class="form-text text-muted mt-2 d-block">Pilih salah satu skala penilaian (1: Tidak Paham s/d 5: Sangat Paham).</small>

                                    @elseif ($p->jenis_pertanyaan == 'teks')
                                        {{-- Untuk pertanyaan tipe teks --}}
                                        <div class="mt-2">
                                            <textarea name="jawaban[{{ $p->id_pertanyaan }}][teks]"
                                                      class="form-control"
                                                      rows="3"
                                                      placeholder="Tuliskan jawaban, kritik, atau saran Anda di sini...">{{ old('jawaban.'.$p->id_pertanyaan.'.teks') }}</textarea>
                                            <small class="form-text text-muted mt-2 d-block">Berikan jawaban Anda dalam bentuk teks.</small>
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        <hr>
                        <div class="text-end">
                            <button type="submit" class="btn btn-success btn-lg shadow-sm">
                                <i class="fas fa-paper-plane me-2"></i> Kirim Feedback
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-muted small text-center bg-light">
                    Perhatian: Feedback yang sudah dikirim tidak dapat diubah kembali.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Script untuk inisialisasi Bootstrap Tooltip --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endpush