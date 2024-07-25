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
    <!-- Estilo local -->
    <link rel="stylesheet" href="{{asset('/dist/css/estilo.css')}}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{asset('/dist/css/adminlte.min.css')}}">
    </head>
    <body class="hold-transition sidebar-mini">
        <!-- Site wrapper -->
        <div class="wrapper">
            {{--  @include('layouts.partials.navbar')  --}}
            {{--  @include('layouts.partials.sidebar')  --}}
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
            {{--  @include('layouts.partials.footer')  --}}
        </div>
        <!-- ./wrapper -->

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


      @yield('scripts')
    </body>
</html>
