@extends('Dosen.layout_dosen')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Manajemen Kuesioner Kustom</h1>

    {{-- Menampilkan pesan sukses atau error --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Filter Mata Kuliah -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pilih Mata Kuliah</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('dosen.kuesioner.index') }}">
                <div class="form-group">
                    <label for="f_matkul">Mata Kuliah yang Diampu:</label>
                    <select name="f_matkul" id="f_matkul" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Pilih Mata Kuliah --</option>
                        @foreach ($matkulDiampu as $matkul)
                            <option value="{{ $matkul->id_matkul }}" {{ $f_matkul == $matkul->id_matkul ? 'selected' : '' }}>
                                {{ $matkul->nama_matkul }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- Hanya tampilkan bagian ini jika mata kuliah sudah dipilih --}}
    @if ($f_matkul && $selectedMatkul)
        <div class="row">
            <!-- Kolom Daftar Pertanyaan -->
            <div class="col-lg-7">
                <!-- Pertanyaan Default (dari Admin) -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-secondary">Pertanyaan Default (dari Admin)</h6>
                    </div>
                    <div class="card-body">
                        @if($pertanyaanDefault->isEmpty())
                            <p>Tidak ada pertanyaan default.</p>
                        @else
                            <ul class="list-group">
                                @foreach ($pertanyaanDefault as $p)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="fw-bold mb-2 d-block text-dark">
                                            {{ $p->teks_pertanyaan }}
                                        </span>
                                        <span class="badge badge-info badge-pill">{{ $p->jenis_pertanyaan }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <!-- Pertanyaan Kustom (Milik Dosen) -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Pertanyaan Kustom untuk: {{ $selectedMatkul->nama_matkul }}</h6>
                    </div>
                    <div class="card-body">
                        @if($pertanyaanKustom->isEmpty())
                            <p>Belum ada pertanyaan kustom untuk mata kuliah ini.</p>
                        @else
                            <ul class="list-group">
                                @foreach ($pertanyaanKustom as $p)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="font-weight-bold text-dark">
                                                {{ $p->teks_pertanyaan }}
                                            </span>
                                            <span class="badge badge-success badge-pill">{{ $p->jenis_pertanyaan }}</span>
                                        </div>
                                        <div class="d-flex">
                                            <a href="{{ route('dosen.kuesioner.edit', $p->id_pertanyaan) }}" class="btn btn-sm btn-warning mr-2"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('dosen.kuesioner.destroy', $p->id_pertanyaan) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pertanyaan ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Kolom Form Tambah Pertanyaan -->
            <div class="col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Tambah Pertanyaan Kustom Baru</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('dosen.kuesioner.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id_matkul" value="{{ $f_matkul }}">

                            <div class="form-group">
                                <label for="teks_pertanyaan">Teks Pertanyaan</label>
                                <textarea name="teks_pertanyaan" id="teks_pertanyaan" class="form-control" rows="3" required>{{ old('teks_pertanyaan') }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="jenis_pertanyaan">Jenis Pertanyaan</label>
                                <select name="jenis_pertanyaan" id="jenis_pertanyaan" class="form-control" required>
                                    <option value="skala" {{ old('jenis_pertanyaan') == 'skala' ? 'selected' : '' }}>Skala (e.g., 1-5)</option>
                                    <option value="teks" {{ old('jenis_pertanyaan') == 'teks' ? 'selected' : '' }}>Jawaban Teks</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> Tambah Pertanyaan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
