@extends('Dosen.layout_dosen')

@section('title', 'Analisis Feedback')

@push('styles')
<style>
    .progress { height: 12px; border-radius: 6px; background-color: #eee; }
    .progress-bar { background-color: #4B49AC; }
    .comment-box {
        max-height: 250px;
        overflow-y: auto;
        background-color: #f9f9ff;
        border: 1px solid #e0e0f0;
        padding: 15px;
        border-radius: 8px;
    }
    .card { border: none; border-radius: 12px; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Analisis: {{ $jadwal->nama_matkul }}</h3>
            <p class="text-muted">Kelas {{ $jadwal->nama_kelas }}</p>
        </div>
        <a href="{{ route('dosen.feedback.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    {{-- Ringkasan Statistik --}}
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3"><i class="fas fa-star fa-2x opacity-50"></i></div>
                    <div>
                        <small class="opacity-75">Rata-rata Skor Keseluruhan</small>
                        <h3 class="fw-bold mb-0">{{ number_format($rataRataKeseluruhan ?? 0, 2) }} / 5.00</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3"><i class="fas fa-users fa-2x opacity-50"></i></div>
                    <div>
                        <small class="opacity-75">Total Responden Unik</small>
                        <h3 class="fw-bold mb-0">{{ $totalResponden }} Mahasiswa</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik Tren --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3">Tren Penilaian Mingguan</div>
        <div class="card-body">
            @if($trenMingguan['labels']->isNotEmpty())
                <canvas id="trenMingguanChart" style="height: 300px;"></canvas>
            @else
                <p class="text-center text-muted">Data tidak cukup untuk grafik tren.</p>
            @endif
        </div>
    </div>

    {{-- Daftar Pertanyaan --}}
    <div class="row">
        @php
            $kategori = [
                ['title' => 'Pertanyaan Admin', 'data' => $pertanyaanDefault, 'color' => 'secondary'],
                ['title' => 'Pertanyaan Kustom Anda', 'data' => $pertanyaanKustom, 'color' => 'primary']
            ];
        @endphp

        @foreach($kategori as $kat)
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-{{ $kat['color'] }}">{{ $kat['title'] }}</h6>
                </div>
                <div class="card-body">
                    @if($kat['data']->isEmpty())
                        <p class="text-center text-dark py-3">Belum ada feedback.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($kat['data'] as $p)
                                <li class="list-group-item py-4 px-0">
                                    <h6 class="fw-bold text-dark mb-3">{{ $loop->iteration }}. {{ $p->teks_pertanyaan }}</h6>
                                    
                                    @if ($p->jenis_pertanyaan == 'skala' && $p->statistik)
                                        <div class="d-flex align-items-center">
                                            <div class="text-primary fw-bold me-3 h4 mb-0">
                                                {{ number_format($p->statistik->rata_rata, 2) }}
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: {{ ($p->statistik->rata_rata / 5) * 100 }}%"></div>
                                                </div>
                                                <small class="text-dark mt-1 d-block">Dari {{ $p->statistik->jumlah_jawaban }} responden</small>
                                            </div>
                                        </div>
                                    @elseif ($p->jenis_pertanyaan == 'teks' && $p->jawaban_teks->isNotEmpty())
                                        <div class="comment-box">
                                            @foreach($p->jawaban_teks as $jawaban)
                                                <div class="p-2 border-bottom mb-1">
                                                    <small class="text-dark">"{{ $jawaban }}"</small>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <small class="text-dark"><i>Belum ada jawaban.</i></small>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const labels = JSON.parse('{!! json_encode($trenMingguan["labels"]) !!}');
        const dataSkor = JSON.parse('{!! json_encode($trenMingguan["data"]) !!}');
        if (document.getElementById('trenMingguanChart')) {
            new Chart(document.getElementById('trenMingguanChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Rata-rata Skor',
                        data: dataSkor,
                        borderColor: '#4B49AC',
                        backgroundColor: 'rgba(75, 73, 172, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 5, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }
    });
</script>
@endsection