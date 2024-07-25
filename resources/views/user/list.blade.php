@extends('layouts.app')
@section('content')
<div class="row justify-content-center">
  <div class="col-md-12">
    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title">Lista de Tarefas para:  "{{ $username->name }}"</h3>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Título da Tarefa</th>
                <th>Processo/Contrato</th>
                <th>Prioridade</th>
                <th>Status</th>
                <th>Ações</th>
              </tr>
            </thead>
              @if ( !$task_list->isEmpty() )
                <tbody>
                @foreach ( $task_list as $task)
                  <tr>
                    <td>{{ $task->task_title }} </td>
                    <td>
                      @if($task->process)
                        <p><span class="badge badge-warning">{{ $task->process->name }}</span></p>
                      @endif
                      @if($task->contract)
                        <p><span class="badge badge-info">{{ $task->contract->number }}</span></p>
                      @endif
                    </td>
                    <td>
                      @if ( $task->priority == 0 )
                        <span class="badge badge-success">Normal</span>
                      @else
                        <span class="badge badge-danger">Alta</span>
                      @endif
                    </td>
                    <td>
                      @if ( !$task->completed )
                        <a href="{{ route('task.completed', ['id' => $task->id]) }}" class="btn btn-warning"> Marcar como completa</a>
                      @else
                        <span class="badge badge-success">Completa</span>
                      @endif
                    </td>
                    <td>
                      <!-- <a href="{{ route('task.edit', ['id' => $task->id]) }}" class="btn btn-primary"> edit </a> -->
                      <a href="{{ route('task.view', ['id' => $task->id]) }}" class="btn btn-primary"> <span class="fa fa-eye" aria-hidden="true"></span> </a>
                      <a href="{{ route('task.delete', ['id' => $task->id]) }}" class="btn btn-danger"><span class="fa fa-trash" aria-hidden="true"></span></a>
                    </td>
                  </tr>
                @endforeach
                </tbody>
              @else
                <p><em>Este usuário não possui tarefas atribuídas ainda.</em></p>
              @endif
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="btn-group">
  <a class="btn btn-default" href="{{ redirect()->getUrlGenerator()->previous() }}">Voltar</a>
</div>




@stop
