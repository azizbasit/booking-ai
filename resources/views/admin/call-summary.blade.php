    @extends('admin.layouts.app')

    @section('styles')
        <!-- Google Font: Source Sans Pro -->
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{ asset('admin/plugins/fontawesome-free/css/all.min.css') }}">
        <!-- DataTables -->
        <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{ asset('admin/dist/css/adminlte.min.css') }}">
    @endsection

    @section('content')
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Call Summaries</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="/admin-page">Home</a></li>
                            <li class="breadcrumb-item active">Call Summaries</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Call Summary List</h3>
                            </div>
                            <div class="card-body">
                                <table id="call-summaries-table" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Physiotherapist ID</th>
                                            <th>Transcript</th>
                                            <th>Summary</th>
                                            <th>Call Recording</th>
                                            <th>Duration (sec)</th>
                                            <th>Cost</th>
                                            <th>Assistant Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($callSummaries as $callSummary)
                                            <tr>
                                                <td>{{ $callSummary->id }}</td>
                                                <td>{{ $callSummary->physio_id }}</td>
                                                <td>
                                                    {{ Str::limit($callSummary->transcript, 50) }}
                                                </td>
                                                <td>
                                                    {{ $callSummary->summary }}
                                                </td>
                                                <td>
                                                    @if ($callSummary->recording_url)
                                                        <button class="btn btn-primary btn-sm"
                                                            data-recording-url="{{ asset('storage/' . $callSummary->recording_url) }}"
                                                            data-toggle="modal" data-target="#audioPlayerModal">
                                                            <i class="fas fa-play"></i> Play Recording
                                                        </button>
                                                    @else
                                                        No Recording
                                                    @endif
                                                </td>
                                                <td>{{ $callSummary->duration_seconds }}</td>
                                                <td>${{ number_format($callSummary->cost, 2) }}</td>
                                                <td>{{ $callSummary->assistant_name }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Audio Player Modal -->
        <div class="modal fade" id="audioPlayerModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Call Recording</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <audio id="audioPlayer" controls class="w-100">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('scripts')
        <!-- jQuery -->
        <script src="{{ asset('admin/plugins/jquery/jquery.min.js') }}"></script>
        <!-- Bootstrap 4 -->
        <script src="{{ asset('admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <!-- DataTables & Plugins -->
        <script src="{{ asset('admin/plugins/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('admin/dist/js/adminlte.min.js') }}"></script>
    @endsection
    @section('script-code')
        <script>
            $(document).ready(function() {
                $('#audioPlayerModal').on('show.bs.modal', function(event) {
                    const button = $(event.relatedTarget);
                    const recordingUrl = button.data('recording-url');
                    const audioPlayer = document.getElementById('audioPlayer');

                    audioPlayer.src = recordingUrl;
                    audioPlayer.load();
                });

                $('#audioPlayerModal').on('hidden.bs.modal', function() {
                    const audioPlayer = document.getElementById('audioPlayer');
                    audioPlayer.pause();
                    audioPlayer.currentTime = 0;
                });
            });
        </script>
    @endsection
