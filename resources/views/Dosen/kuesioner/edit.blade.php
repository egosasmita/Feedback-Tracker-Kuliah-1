@extends('Dosen.layout_dosen')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Pertanyaan Kustom</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Pertanyaan</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('dosen.kuesioner.update', $pertanyaan->id_pertanyaan) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="teks_pertanyaan">Teks Pertanyaan</label>
                    <textarea name="teks_pertanyaan" id="teks_pertanyaan" class="form-control" rows="4" required>{{ old('teks_pertanyaan', $pertanyaan->teks_pertanyaan) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="jenis_pertanyaan">Jenis Pertanyaan</label>
                    <select name="jenis_pertanyaan" id="jenis_pertanyaan" class="form-control" required>
                        <option value="skala" {{ old('jenis_pertanyaan', $pertanyaan->jenis_pertanyaan) == 'skala' ? 'selected' : '' }}>Skala (e.g., 1-5)</option>
                        <option value="teks" {{ old('jenis_pertanyaan', $pertanyaan->jenis_pertanyaan) == 'teks' ? 'selected' : '' }}>Jawaban Teks</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection