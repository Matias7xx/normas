@extends('layouts.app')
@section('page-title')
  Listagem de Usuários
@endsection
@section('header-content')
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0 text-dark">Listagem de Usuários</h1>
    </div><!-- /.col -->
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Listagem de Usuários</li>
      </ol>
    </div><!-- /.col -->
  </div>
@endsection
@section('content')
  <div class="col-md-11">
    <div class="new_project">
      <a type="button" class="btn btn-primary" href="{{route('user.create')}}"><span class="fa fa-plus" aria-hidden="true"></span> Novo Usuário</a>
    </div>
  </div>
  <br/>
  <div class="row justify-content-center">
    <div class="col-md-11">
      <div class="card card-primary">
        <div class="card-header">USUÁRIOS</div>
        <div class="card-body">
          <table id="usersList" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>NOME</th>
                <th>CARGO</th>
                <th>CPF</th>
                <th>TELEFONE</th>
                <th>EMAIL</th>
                <th>PERFIL</th>
                <th>STATUS</th>
                <th>AÇÕES</th>
              </tr>
            </thead>
            @if (!$users->isEmpty())
              <tbody>
                @foreach ( $users as $user)
                  @if ( $user->id == 1 )  @continue
                  @endif
                  <tr>
                    <td><a href="{{ route('user.list', ['id'=> $user->id] ) }}">{{ $user->name }}</a></td>
                    <td>{{ $user->cargo ? $user->cargo : 'NÃO ATRIBUÍDO' }}</td>
                    <td>{{ $user->cpf ? $user->cpf : 'NÃO ATRIBUÍDO' }}</td>
                    <td>{{ $user->telefone ? $user->telefone : 'NÃO ATRIBUÍDO' }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role ? $user->role->name : 'NÃO ATRIBUÍDO' }}</td>
                    <td>
                      @if ( !$user->active )
                        <a href="{{ route('user.activate', ['id' => $user->id]) }}" class="btn btn-warning"> Ativar Usuário</a>
                      @else
                        <a href="{{ route('user.disable', ['id' => $user->id]) }}" class="btn btn-warning"> Inativar Usuário</a>
                        <span class="badge badge-success">Ativo</span>
                      @endif
                    </td>
                    <td>
                      <a href="{{ route('user.edit', ['id' => $user->id]) }}" class="btn btn-primary mb-2"><span class="fa fa-edit fa-fw" aria-hidden="true"></span></a>
                      <a href="{{ route('user.delete', ['id' => $user->id]) }}" class="btn btn-danger mb-2" Onclick="return ConfirmDelete();"><span class="fa fa-trash fa-fw" aria-hidden="true"></span></a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            @else
              <p><em>Não há usuários cadastrados.</em></p>
            @endif
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('styles')
  @can ('admin')
    <link rel="stylesheet" href="/adminlte/plugins/datatables/plugins/css/buttons.dataTables.min.css">
    <style media="screen">
      div .dt-buttons{
        float : left;
      }
      .dataTables_length{
        float : left;
      }
    </style>
  @endcan
@endsection
@section('scripts')
  @can ('admin')
    <script type="text/javascript" src="/adminlte/plugins/datatables/plugins/js/dataTables.buttons.min.js"></script>
    	<script type="text/javascript" src="/adminlte/plugins/datatables/plugins/js/buttons.flash.min.js"></script>
    	<script type="text/javascript" src="/adminlte/plugins/datatables/plugins/js/jszip.min.js"></script>
    	<script type="text/javascript" src="/adminlte/plugins/datatables/plugins/js/pdfmake.min.js"></script>
    	<script type="text/javascript" src="/adminlte/plugins/datatables/plugins/js/vfs_fonts.js"></script>
    	<script type="text/javascript" src="/adminlte/plugins/datatables/plugins/js/buttons.html5.min.js"></script>
    	<script type="text/javascript" src="/adminlte/plugins/datatables/plugins/js/buttons.print.min.js"></script>
    	<script type="text/javascript" src="/adminlte/plugins/datatables/plugins/js/buttons.colVis.min.js"></script>
  @endcan
  <!-- scripts da página -->
  <script>
    function ConfirmDelete()
    {
      var x = confirm("Você tem certeza? A exclusão de um usuário também excluirá todas as tarefas associadas.");
      if (x)
      return true;
      else
      return false;
    }
    $(function () {
        $("#usersList").DataTable({
          @can ('admin')
            "bJQueryUI": true,
            "pagingType": "full_numbers",
            dom: 'Blfrtip',
            buttons: [
              {
                extend: 'excelHtml5',
                text: 'EXCEL',
                exportOptions: {
                  columns: ':visible'
                },
                messageTop: 'Listagem de Usuários.',
                messageBottom: 'Informação Sigilosa.'
              }
            ],
          @endcan
          "language": {
             "sEmptyTable": "Nenhum registro encontrado",
             "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
             "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
             "sInfoFiltered": "(Filtrados de _MAX_ registros)",
             "sInfoPostFix": "",
             "sInfoThousands": ".",
             "sLengthMenu": "_MENU_ por página",
             "sLoadingRecords": "Carregando...",
             "sProcessing": "Processando...",
             "sZeroRecords": "Nenhum registro encontrado",
             "sSearch": "Filtrar",
             "oPaginate": {
                 "sNext": "Próximo",
                 "sPrevious": "Anterior",
                 "sFirst": "Primeiro",
                 "sLast": "Último"
             },
             "oAria": {
                 "sSortAscending": ": Ordenar colunas de forma ascendente",
                 "sSortDescending": ": Ordenar colunas de forma descendente"
             }
           }
        });
      });
  </script>
@endsection
