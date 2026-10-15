@extends('Admin.layout_admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Manajemen Kuesioner Default</h1>

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

    <div class="row">
        <!-- Kolom Daftar Pertanyaan -->
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Pertanyaan Default</h6>
                </div>
                <div class="card-body">
                    @if($pertanyaanDefault->isEmpty())
                        <p>Belum ada pertanyaan default yang dibuat.</p>
                    @else
                        <ul class="list-group">
                            @foreach ($pertanyaanDefault as $p)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="font-weight-bold text-dark">
                                            {{ $p->teks_pertanyaan }}
                                        </span>
                                        <span class="badge badge-info badge-pill">{{ $p->jenis_pertanyaan }}</span>
                                    </div>
                                    <div class="d-flex">
                                        {{-- Tombol Edit akan mengarah ke halaman edit --}}
                                        <a href="{{ route('admin.kuesioner.edit', $p->id_pertanyaan) }}" class="btn btn-sm btn-warning mr-2"><i class="fas fa-edit"></i></a>
                                        
                                        {{-- Form untuk tombol Delete --}}
                                        <form action="{{ route('admin.kuesioner.destroy', $p->id_pertanyaan) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pertanyaan default ini?');">
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
                    <h6 class="m-0 font-weight-bold text-primary">Tambah Pertanyaan Default Baru</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.kuesioner.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="teks_pertanyaan">Teks Pertanyaan</label>
                            <textarea name="teks_pertanyaan" id="teks_pertanyaan" class="form-control" rows="4" required>{{ old('teks_pertanyaan') }}</textarea>
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
</div>
@endsection