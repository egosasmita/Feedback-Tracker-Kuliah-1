@extends('Dosen.layout_dosen')

@section('title', 'Analisis Feedback Mata Kuliah')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Analisis Feedback</h1>
        <p class="text-muted small mb-0">Pilih mata kuliah untuk melihat detail analisis feedback dari mahasiswa.</p>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Mata Kuliah Anda</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Mata Kuliah</th>
                            <th>Kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftarMatkul as $matkul)
                        <tr>
                            <td>{{ $matkul->nama_matkul }}</td>
                            <td>{{ $matkul->nama_kelas }}</td>
                            <td>
                                <a href="{{ route('dosen.feedback.show', $matkul->id_jadwal) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-chart-bar fa-sm"></i> Lihat Analisis
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">Anda tidak mengampu mata kuliah apapun.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection