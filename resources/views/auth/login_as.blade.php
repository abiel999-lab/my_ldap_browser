{{-- parent layout --}}
@extends('layouts.1')

{{-- header page dan breadcrumb --}}
@section('page_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Login As</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">
                            <a href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active">
                            <a href="#">Daftar User Login As</a>
                        </li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
@endsection


{{-- main content --}}
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mt-1">Daftar User Login As
            </h3>
        </div>

        <div class="card-body">
            <div>
                <!-- tambah table class dt untuk pakai datatable -->
                <table
                    id="table"
                    class="table table-bordered table-stripped table-hover"
                >
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th width="100">Tipe</th>
                            <th width="100">Kode</th>
                            <th width="150">Nama</th>
                            <th width="100">Email</th>
                            <th width="150">Role</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@push('page_script')
    <script type="text/javascript">
        $(function() {
            //$.fn.dataTable.ext.errMode = 'none';
            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('api.auth.loginas') }}",
                    type: 'GET',
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'tipe',
                        name: 'tipe',
                    },
                    {
                        data: 'kode',
                        name: 'kode',
                    },
                    {
                        data: 'nama',
                        name: 'nama',
                    },
                    {
                        data: 'email',
                        name: 'email',
                    },
                    {
                        data: 'role',
                        name: 'role',
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                    },
                ],
                order: [
                    [1, 'asc'],
                    [3, 'asc'],
                    [2, 'asc']
                ],
            });
        });
    </script>
@endpush
