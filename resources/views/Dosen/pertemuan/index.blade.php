@extends('Dosen.layout_dosen')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h3 class="fw-bold">{{ $jadwal->nama_matkul }} ({{ $jadwal->nama_kelas }})</h3>
        <p class="text-muted">Kelola sesi pertemuan untuk menerima feedback mahasiswa.</p>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white fw-bold">Buka Pertemuan Baru</div>
                <div class="card-body">
                    <form action="{{ route('dosen.pertemuan.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_jadwal" value="{{ $jadwal->id_jadwal }}">
                        <div class="mb-3">
                            <label class="small fw-bold">Pertemuan Ke-</label>
                            <input type="number" name="pertemuan_ke" class="form-control" placeholder="Contoh: 1" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Materi / Topik</label>
                            <textarea name="materi" class="form-control" rows="3" placeholder="Apa yang dibahas hari ini?" required></textarea>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <h6 class="fw-bold">Pertanyaan Kustom (Opsional)</h6>
                            <p class="small text-muted mb-2">Tambahkan pertanyaan spesifik untuk pertemuan ini.</p>
                            <div id="pertanyaan-kustom-container">
                                {{-- Konten dinamis via JS --}}
                            </div>
                            <button type="button" id="btn-tambah-pertanyaan" class="btn btn-sm btn-outline-primary mt-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                </svg>
                                Tambah Pertanyaan
                            </button>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold mt-3">Buka Sesi Feedback</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Pertemuan</th>
                                <th>Materi</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pertemuan as $p)
                            <tr>
                                <td class="ps-4 fw-bold">Ke-{{ $p->pertemuan_ke }}</td>
                                <td>{{ $p->materi }}</td>
                                <td>{{ date('d M Y', strtotime($p->tanggal)) }}</td>
                                <td>
                                    <span class="badge {{ $p->status == 'terbuka' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($p->status) }}
                                    </span>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('pertanyaan-kustom-container');
    const addButton = document.getElementById('btn-tambah-pertanyaan');
    let questionIndex = 0;

    addButton.addEventListener('click', function () {
        const questionId = `q-kustom-${questionIndex}`;
        const newQuestionHTML = `
            <div class="card mb-2 border-dashed" id="${questionId}">
                <div class="card-body p-2">
                     <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Pertanyaan Kustom Baru</span>
                        <button type="button" class="btn-close" aria-label="Close" onclick="document.getElementById('${questionId}').remove()"></button>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="pertanyaan_kustom[${questionIndex}][teks]" class="form-control form-control-sm" placeholder="Tulis teks pertanyaan..." required>
                    </div>
                    <div>
                        <select name="pertanyaan_kustom[${questionIndex}][jenis]" class="form-select form-select-sm" required>
                            <option value="skala">Jenis: Skala Paham (1-5)</option>
                            <option value="teks">Jenis: Jawaban Teks Bebas</option>
                        </select>
                    </div>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', newQuestionHTML);
        questionIndex++;
    });
});
</script>
@endsection