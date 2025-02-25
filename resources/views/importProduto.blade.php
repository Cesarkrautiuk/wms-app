@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3 m-1">Importar Produto</h5>
        <form id="uploadForm" action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="input-group mb-3">
                <input type="file" class="form-control bg-dark" required name="excel_file"
                       id="inputGroupFile02">
                <button class="btn btn-outline-secondary" type="submit" id="inputGroupFileAddon04">Enviar</button>
            </div>
        </form>
    </div>
    </div>
    </div>
@endsection
