<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AI Dental Appointments')</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css') }}">">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet"
        href="{{ asset('admin/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">">
    <!-- JQVMap -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/jqvmap/jqvmap.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('admin/dist/css/adminlte.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/daterangepicker/daterangepicker.css') }}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/summernote/summernote-bs4.min.css') }}">>
    @yield('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Preloader -->
        {{-- <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="admin/dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
  </div> --}}

        <!-- Navbar -->
        @include('admin.layouts.navbar')
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        @include('admin.layouts.sidebar')

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            @yield('content')
        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 3.1.0
            </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="{{ asset('admin/plugins/jquery/jquery.min.js') }}"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="{{ asset('admin/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- ChartJS -->
    <script src="{{ asset('admin/plugins/chart.js/Chart.min.js') }}"></script>
    <!-- Sparkline -->
    <script src="{{ asset('admin/plugins/sparklines/sparkline.js') }}"></script>
    <!-- JQVMap -->
    <script src="{{ asset('admin/plugins/jqvmap/jquery.vmap.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
    <!-- jQuery Knob Chart -->
    <script src="{{ asset('admin/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
    <!-- daterangepicker -->
    <script src="{{ asset('admin/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="{{ asset('admin/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <!-- Summernote -->
    <script src="{{ asset('admin/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <!-- overlayScrollbars -->
    <script src="{{ asset('admin/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('admin/dist/js/adminlte.js') }}"></script>

    @yield('script')

    @yield('script-code')


    <!-- Call Modal -->
    <div class="modal fade" id="callModal" tabindex="-1" role="dialog" aria-labelledby="callModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="callModalLabel">Make a Call</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="callStatus" class="alert" style="display: none;"></div>
                    <div class="form-group">
                        <label for="phoneNumber">Phone Number</label>
                        <input type="tel" class="form-control" id="phoneNumber" placeholder="Enter phone number">
                    </div>
                    <div class="form-group">
                        <label for="callerName">Name (Optional)</label>
                        <input type="text" class="form-control" id="callerName" placeholder="Enter name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="makeCallBtn">
                        <i class="fas fa-phone mr-1"></i> Call
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- AI Assistant Modal -->
    <div class="modal fade" id="aiAssistantModal" tabindex="-1" role="dialog" aria-labelledby="aiAssistantModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="aiAssistantForm" action="{{ route('ai-assistants-profiles.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="aiAssistantModalLabel">AI Assistant</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Step 1 -->
                        <div id="aiStep1">
                            <div class="mb-3">
                                <label class="form-label">Physiotherapist Name:</label>
                                <input type="text" name="name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Area of Specialization</label>
                                <textarea name="specialization" class="form-control"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Experience (Years)</label>
                                <input type="number" name="experience" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Business Email Address:</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Business Phone Number:</label>
                                <input type="tel" name="business_phone" class="form-control">
                            </div>
                            <button type="button" class="btn btn-primary" onclick="aiNextStep()">Next Step</button>
                        </div>
                        <!-- Step 2 -->
                        <div id="aiStep2" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Clinic Name</label>
                                <input type="text" name="clinic_name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Working Hours</label>
                                <div class="row">
                                    <div class="col">
                                        <select name="working_hours_start" class="form-control">
                                            <option value="">From</option>
                                            @for ($i = 6; $i <= 23; $i++)
                                                <option value="{{ sprintf('%02d:00', $i) }}">
                                                    {{ sprintf('%02d:00', $i) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col">
                                        <select name="working_hours_end" class="form-control">
                                            <option value="">To</option>
                                            @for ($i = 6; $i <= 23; $i++)
                                                <option value="{{ sprintf('%02d:00', $i) }}">
                                                    {{ sprintf('%02d:00', $i) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Working Days</label>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="working_days[]"
                                                value="Monday" id="monday">
                                            <label class="form-check-label" for="monday">Monday</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="working_days[]"
                                                value="Tuesday" id="tuesday">
                                            <label class="form-check-label" for="tuesday">Tuesday</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="working_days[]"
                                                value="Wednesday" id="wednesday">
                                            <label class="form-check-label" for="wednesday">Wednesday</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="working_days[]"
                                                value="Thursday" id="thursday">
                                            <label class="form-check-label" for="thursday">Thursday</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="working_days[]"
                                                value="Friday" id="friday">
                                            <label class="form-check-label" for="friday">Friday</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="working_days[]"
                                                value="Saturday" id="saturday">
                                            <label class="form-check-label" for="saturday">Saturday</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="working_days[]"
                                                value="Sunday" id="sunday">
                                            <label class="form-check-label" for="sunday">Sunday</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Clinic Address</label>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Street Address *</label>
                                        <input type="text" name="clinic_street" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">City *</label>
                                        <input type="text" name="clinic_city" class="form-control" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Region *</label>
                                        <input type="text" name="clinic_region" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Postal Code *</label>
                                        <input type="text" name="clinic_postal_code" class="form-control"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control"></textarea>
                            </div>
                            <button type="button" class="btn btn-secondary" onclick="aiPrevStep()">Back</button>
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Call Script -->
    <!-- Call Script -->
    <script>
        document.getElementById('makeCallBtn').addEventListener('click', function() {
            const phoneNumber = document.getElementById('phoneNumber').value;
            const callerName = document.getElementById('callerName').value || 'Patient';
            const statusDiv = document.getElementById('callStatus');

            if (!phoneNumber) {
                statusDiv.className = 'alert alert-danger';
                statusDiv.textContent = 'Please enter a phone number';
                statusDiv.style.display = 'block';
                return;
            }

            // Show loading state
            const originalBtnText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Initiating...';
            this.disabled = true;
            statusDiv.style.display = 'none';

            // Make AJAX call to our backend
            fetch('{{ route('initiate.call') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        phone_number: phoneNumber,
                        name: callerName
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        statusDiv.className = 'alert alert-success';
                        statusDiv.textContent = data.message;

                        // Open call monitoring in a new window
                        const callWindow = window.open('{{ route('call.monitor') }}?call_id=' + data.data.id,
                            'VapiCall',
                            'width=600,height=400,resizable=yes');

                        // Handle if popup was blocked
                        if (!callWindow || callWindow.closed || typeof callWindow.closed == 'undefined') {
                            statusDiv.className = 'alert alert-warning';
                            statusDiv.textContent =
                                'Call initiated but popup was blocked. Please allow popups for this site.';
                        }
                    } else {
                        statusDiv.className = 'alert alert-danger';
                        statusDiv.textContent = data.message || 'Failed to initiate call';
                    }
                    statusDiv.style.display = 'block';
                })
                .catch(error => {
                    statusDiv.className = 'alert alert-danger';
                    statusDiv.textContent = 'Error connecting to server';
                    statusDiv.style.display = 'block';
                    console.error('Error:', error);
                })
                .finally(() => {
                    // Reset button state
                    this.innerHTML = originalBtnText;
                    this.disabled = false;
                });
        });

        function aiNextStep() {
            document.getElementById('aiStep1').style.display = 'none';
            document.getElementById('aiStep2').style.display = 'block';
        }

        function aiPrevStep() {
            document.getElementById('aiStep2').style.display = 'none';
            document.getElementById('aiStep1').style.display = 'block';
        }
        $('#aiAssistantModal').on('hidden.bs.modal', function() {
            document.getElementById('aiStep1').style.display = 'block';
            document.getElementById('aiStep2').style.display = 'none';
            document.getElementById('aiAssistantForm').reset();
        });
        $('#aiAssistantForm').on('submit', function(e) {
            e.preventDefault();
            let $submitBtn = $(this).find('button[type="submit"]');
            let originalBtnText = $submitBtn.html();
            $submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Submitting...').prop('disabled', true);
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(res) {
                    let alertType = res.status === 'success' ? 'alert-success' : 'alert-danger';
                    let message = res.message || 'Something went wrong.';
                    showNotification(message, alertType);
                    $('#aiAssistantModal').modal('hide');
                    // if (res.status === 'success') {
                    //     $('#aiAssistantModal').modal('hide');
                    //     // location.reload();
                    // }
                },
                error: function(xhr) {
                    let message = 'Server error. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        message = xhr.responseText;
                    }
                    showNotification(message, 'alert-danger');
                }
            }).always(function() {
                $submitBtn.html(originalBtnText).prop('disabled', false);
            });
        });

        // Add this function if not already present
        function showNotification(message, alertType) {
            let notif = $('<div class="alert ' + alertType + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button></div>');
            $('body').append(notif);
            setTimeout(function() {
                notif.alert('close');
            }, 4000);
        }
    </script>
</body>

</html>
