@extends('layouts.app')

@section('header-content')
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1>NORMAS</h1>
    </div>
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Página em Branco</li>
      </ol>
    </div>
  </div>
@endsection

@section('content')
    <!-- Main content -->
    <section class="content">
      <!-- Default box -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Título</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-md-3">
                      <div class="image-container">
                      </div>
                </div>
                <div class="col-md-9">
                    <button class="btn btn-secondary" type="button" id="colect_fingerprint"> <span class="fa fa-fingerprint" aria-hidden="true"></span> Coletar digital</button>
                </div>
                <input type='text' id='image_base64' name='image_base64'>
              </div>

        </div>
        <!-- /.card-body -->
        <div class="card-footer">
          Rodapé
        </div>
        <!-- /.card-footer-->
      </div>
      <!-- /.card -->

    </section>
    <!-- /.content -->
@endsection


@section('scripts')
  <script>
    $(document).ready(function() {

        $("#colect_fingerprint").click(function(event) {

            $("#modal-lg").modal({
              backdrop: 'static',
              keyboard: false
            });

            $.ajax({
                type: 'GET',
                url: '{{env("BIOMETRICS_URL")}}/v1/biometria',
                headers: { 'Access-Control-Allow-Origin':'http://localhost/v1/biometria'},
                success: function(data) {
                    //console.log(data);
                    var imageBase64 = data.imagemBs64;
                    $(".image-container").html("<img src='data:image/png;base64," + imageBase64 + "' witdh=150 height=200/>");
                    $("#image_base64").val(imageBase64);
                    $("#modal-lg").modal('hide');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                  $("#modal-lg").modal('hide');
                }
            });

        });

        $("#colect_fingerprint").trigger('click');

    });
  </script>
  @endsection
