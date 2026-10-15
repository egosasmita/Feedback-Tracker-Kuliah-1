@extends('Admin.layout_admin')

@section('title', 'Detail Analisis Feedback')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('admin.feedback.index') }}" class="text-decoration-none small fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Laporan Utama
        </a>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold m-0">Analisis Feedback: {{ $jadwal->nama_matkul }}</h3>
            <p class="text-muted m-0">Dosen: {{ $jadwal->nama_dosen }}</p>
        </div>
    </div>

    @forelse($pertanyaan as $p)
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold m-0">{{ $p->teks_pertanyaan }}</h5>
                <small class="text-muted">
                    Tipe: {{ ucfirst($p->jenis_pertanyaan) }} | 
                    Sumber: {{ $p->id_matkul ? 'Kustom (Dosen)' : 'Default (Admin)' }}
                </small>
            </div>
            <div class="card-body p-4">
                @if($p->jenis_pertanyaan == 'skala')
                    @if($p->statistik && $p->statistik->jumlah_jawaban > 0)
                        <div class="d-flex align-items-center">
                            <div class="display-4 fw-bold text-primary me-3">
                                {{ number_format($p->statistik->rata_rata, 2) }}
                            </div>
                            <div>
                                <div class="fw-bold">Rata-rata Skor</div>
                                <div class="text-muted">{{ $p->statistik->jumlah_jawaban }} Responden</div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted fst-italic">Belum ada jawaban untuk pertanyaan ini.</p>
                    @endif
                @else
                    @if($p->jawaban_teks && $p->jawaban_teks->isNotEmpty())
                        <ul class="list-group list-group-flush">
                            @foreach($p->jawaban_teks as $jawaban)
                                <li class="list-group-item fst-italic">"{{ $jawaban }}"</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted fst-italic">Belum ada jawaban untuk pertanyaan ini.</p>
                    @endif
                @endif
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-5">
                <p class="text-muted m-0">Belum ada pertanyaan kuesioner yang dibuat untuk mata kuliah ini atau secara default.</p>
            </div>
        </div>
    @endforelse
</div>
@endsection