<!DOCTYPE html>
<html lang="pt_BR">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name')}} | PCPB</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{asset('/plugins/fontawesome-free/css/all.min.css')}}">

    <!-- datatables -->
    <link rel="stylesheet" href="{{asset('../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css')}}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
    
    <link rel="stylesheet" href="{{asset('/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css')}}">
    <link rel="stylesheet" href="{{asset('/plugins/bs-stepper/css/bs-stepper.min.css')}}">
    <link rel="stylesheet" href="{{asset('/plugins/dropzone/min/dropzone.min.css')}}">
    
    <!-- Estilo local -->
    <link rel="stylesheet" href="{{asset('/dist/css/estilo.css')}}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{asset('/dist/css/adminlte.min.css')}}">
    </head>
    <body class='hold-transition sidebar-mini'>

        <!-- Site wrapper -->
        <div class="wrapper">
            @include('layouts.partials.navbar')
            @include('layouts.partials.sidebar')
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                <div class="container-fluid">
                    @yield('header-content')
                </div><!-- /.container-fluid -->
                </section>
                @yield('content')
            </div>
            <!-- /.content-wrapper -->
            @include('layouts.partials.footer')
        </div>
        <!-- ./wrapper -->

        <style>
            .wrapper {
                min-height: 100vh; /* Garante que o wrapper ocupe pelo menos a altura da tela */
                display: flex;
                flex-direction: column;
            }

            main {
                flex: 1 0 auto; /* Faz o conteúdo principal crescer e empurrar o footer para baixo */
            }

            .main-footer {
                flex-shrink: 0; /* Impede que o footer encolha */
            }
        </style>

    <!-- jQuery -->
    <script src="{{asset('/plugins/jquery/jquery.min.js')}}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{asset('/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <!-- AdminLTE App -->
    <script src="{{asset('/dist/js/adminlte.min.js')}}"></script>
    <!-- Jquery Toast -->
    <script src="{{asset('/plugins/toastr/toastr.min.js')}}"></script>
    <!-- Jquery Masked Input Plugin -->
    <script src="{{asset('/plugins/mask/jqueryMaskedInputPlugin.min.js')}}"></script>
    
    <script src="{{asset('/plugins/inputmask/jquery.inputmask.min.js')}}"></script>
    <script src="{{asset('/plugins/bs-stepper/js/bs-stepper.min.js')}}"></script>
    <script src="{{asset('/plugins/dropzone/min/dropzone.min.js')}}"></script>
    
    <!-- Select2 -->
    <script src="{{asset('/plugins/select2/js/select2.full.min.js')}}"></script>

    <script src="{{asset('../../plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
    <script src="{{asset('../../plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
    <script src="{{asset('../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
    <script src="{{asset('../../plugins/datatables-buttons/js/dataTables.buttons.min.js')}}"></script>
    <script src="{{asset('../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js')}}"></script>
    <script src="{{asset('../../plugins/jszip/jszip.min.js')}}"></script>
    <script src="{{asset('../../plugins/pdfmake/pdfmake.min.js')}}"></script>
    <script src="{{asset('../../plugins/pdfmake/vfs_fonts.js')}}"></script>
    <script src="{{asset('../../plugins/datatables-buttons/js/buttons.html5.min.js')}}"></script>
    <script src="{{asset('../../plugins/datatables-buttons/js/buttons.print.min.js')}}"></script>
    <script src="{{asset('../../plugins/datatables-buttons/js/buttons.colVis.min.js')}}"></script>

    {{--  função abrir página em modal  --}}
    <script src="{{asset('/dist/js/funcaoJs.js')}}"></script>

    <script type="text/javascript">
        $('document').ready(function() {
          @if (session('error'))
            $(document).Toasts('create', {
                title: "ATENÇÃO!",
                class: 'bg-danger',
                autohide: true,
                delay: 5000,
                position: 'topRight',
                body: "{{ session('error') }}"
            })
          @endif
          @if (count($errors) > 0)
            @foreach ($errors->all() as $key => $error)
                $(document).Toasts('create', {
                    title: 'ATENÇÃO!',
                    class: 'bg-danger',
                    autohide: true,
                    delay: 5000,
                    position: 'topRight',
                    body: "{{ $error }}"
                })
            @endforeach
          @endif
          @if (session('success'))
            $(document).Toasts('create', {
                title: "ATENÇÃO!",
                class: 'bg-success',
                autohide: true,
                delay: 5000,
                position: 'topRight',
                body: "{{ session('success') }}"
            })
          @endif
        });
        $('.input_file').on('change',function(){
            if($(this).get(0).files.length > 0){ // only if a file is selected
              var fileSize = $(this).get(0).files[0].size;
              console.log(fileSize);
              if (fileSize > 20480000) {
                $('.input_file').val('');
                $.toast({
                  heading: 'Atenção:',
                  text: 'Arquivo maior que o permitido. Os Arquivos devem ter no máximo 20MB.',
                  position: 'top-right',
                  stack: 5,
                  icon: 'error',
                  showHideTransition: 'plain',
                  hideAfter: false   // in milli seconds
                });
              }
            }
          });

          $(function () {
            $("#example1").DataTable({
              "responsive": true, "lengthChange": false, "autoWidth": false,
              "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
            $('#example2').DataTable({
              "paging": true,
              "lengthChange": false,
              "searching": false,
              "ordering": true,
              "info": true,
              "autoWidth": false,
              "responsive": true,
            });
          });
      </script>

      <script>
        $(function () {
            //Verifica se o plugin Select2 está disponível
            if ($.fn.select2) {
                //Initialize Select2 Elements
                $('.select2').select2();

                //Initialize Select2 Elements
                $('.select2bs4').select2({
                    theme: 'bootstrap4'
                });
            }

            //Verifica se o plugin inputmask está disponível
            if ($.fn.inputmask) {
                //Datemask dd/mm/yyyy
                $('#datemask').inputmask('dd/mm/yyyy', { 'placeholder': 'dd/mm/yyyy' });
                //Datemask2 mm/dd/yyyy
                $('#datemask2').inputmask('mm/dd/yyyy', { 'placeholder': 'mm/dd/yyyy' });
                //Money Euro
                $('[data-mask]').inputmask();
            }

            //Verifica se o plugin datetimepicker está disponível
            if ($.fn.datetimepicker) {
                //Date picker
                $('#reservationdate').datetimepicker({
                    format: 'L'
                });

                //Date and time picker
                $('#reservationdatetime').datetimepicker({ icons: { time: 'far fa-clock' } });
                
                //Timepicker
                $('#timepicker').datetimepicker({
                    format: 'LT'
                });
            }

            //Verifica se o plugin daterangepicker está disponível
            if ($.fn.daterangepicker) {
                //Date range picker
                $('#reservation').daterangepicker();
                
                //Date range picker with time picker
                $('#reservationtime').daterangepicker({
                    timePicker: true,
                    timePickerIncrement: 30,
                    locale: {
                        format: 'MM/DD/YYYY hh:mm A'
                    }
                });
                
                //Date range as a button
                $('#daterange-btn').daterangepicker(
                {
                    ranges   : {
                    'Today'       : [moment(), moment()],
                    'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days' : [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month'  : [moment().startOf('month'), moment().endOf('month')],
                    'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    },
                    startDate: moment().subtract(29, 'days'),
                    endDate  : moment()
                },
                function (start, end) {
                    $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                });
            }

            //Verifica se o plugin bootstrapDualListbox está disponível
            if ($.fn.bootstrapDualListbox) {
                //Bootstrap Duallistbox
                $('.duallistbox').bootstrapDualListbox();
            }

            //Verifica se o plugin colorpicker está disponível
            if ($.fn.colorpicker) {
                //Colorpicker
                $('.my-colorpicker1').colorpicker();
                //color picker with addon
                $('.my-colorpicker2').colorpicker();

                $('.my-colorpicker2').on('colorpickerChange', function(event) {
                    $('.my-colorpicker2 .fa-square').css('color', event.color.toString());
                });
            }

            //Verifica se o plugin bootstrapSwitch está disponível
            if ($.fn.bootstrapSwitch) {
                $("input[data-bootstrap-switch]").each(function(){
                    $(this).bootstrapSwitch('state', $(this).prop('checked'));
                });
            }
        });

        // BS-Stepper Init
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Stepper !== 'undefined' && document.querySelector('.bs-stepper')) {
                window.stepper = new Stepper(document.querySelector('.bs-stepper'));
            }
        });

        // DropzoneJS Demo Code Start
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Dropzone !== 'undefined') {
                Dropzone.autoDiscover = false;
                
                var templateElement = document.querySelector("#template");
                if (templateElement) {
                    var previewNode = templateElement;
                    previewNode.id = "";
                    var previewTemplate = previewNode.parentNode.innerHTML;
                    previewNode.parentNode.removeChild(previewNode);
                    
                    var previewsContainer = document.querySelector("#previews");
                    var clickableElement = document.querySelector(".fileinput-button");
                    
                    if (previewsContainer && clickableElement) {
                        var myDropzone = new Dropzone(document.body, {
                            url: "/target-url", 
                            thumbnailWidth: 80,
                            thumbnailHeight: 80,
                            parallelUploads: 20,
                            previewTemplate: previewTemplate,
                            autoQueue: false,
                            previewsContainer: "#previews", 
                            clickable: ".fileinput-button"
                        });

                        myDropzone.on("addedfile", function(file) {
                            var startButton = file.previewElement.querySelector(".start");
                            if (startButton) {
                                startButton.onclick = function() { myDropzone.enqueueFile(file); };
                            }
                        });

                        myDropzone.on("totaluploadprogress", function(progress) {
                            var progressBar = document.querySelector("#total-progress .progress-bar");
                            if (progressBar) {
                                progressBar.style.width = progress + "%";
                            }
                        });

                        myDropzone.on("sending", function(file) {
                            var totalProgress = document.querySelector("#total-progress");
                            var startButton = file.previewElement.querySelector(".start");
                            
                            if (totalProgress) {
                                totalProgress.style.opacity = "1";
                            }
                            
                            if (startButton) {
                                startButton.setAttribute("disabled", "disabled");
                            }
                        });

                        myDropzone.on("queuecomplete", function(progress) {
                            var totalProgress = document.querySelector("#total-progress");
                            if (totalProgress) {
                                totalProgress.style.opacity = "0";
                            }
                        });

                        // Setup the buttons for all transfers
                        var startButton = document.querySelector("#actions .start");
                        var cancelButton = document.querySelector("#actions .cancel");
                        
                        if (startButton) {
                            startButton.onclick = function() {
                                myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED));
                            };
                        }
                        
                        if (cancelButton) {
                            cancelButton.onclick = function() {
                                myDropzone.removeAllFiles(true);
                            };
                        }
                    }
                }
            }
        });
        // DropzoneJS Demo Code End
      </script>

      @yield('scripts')
    </body>
</html>