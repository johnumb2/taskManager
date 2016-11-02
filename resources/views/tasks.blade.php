@extends('layout')

@section('content')
    <div class="row">
        <div class="col-xs-4">
            <div class="panel panel-primary">
                <div class="panel-heading">Tasks</div>
                <div class="panel-body sortable" id="available">

                </div>
            </div>
        </div>
        <div class="col-xs-4">
            <div class="panel panel-success">
                <div class="panel-heading">In Progress</div>
                <div class="panel-body sortable" id="in-progress">

                </div>
            </div>
        </div>
        <div class="col-xs-4">
            <div class="panel panel-default">
                <div class="panel-heading">Finished</div>
                <div class="panel-body sortable" id="finished">

                </div>
            </div>
        </div>
    </div>

    <div class="task-template hidden">
        <div class="row task">
            <div class="task-grip"><i class="fa fa-grip"></i></div>
            <div class="task-heading"></div>
            <div class="task-body-min"></div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="Task" tabindex="-1" role="dialog" aria-labelledby="TaskLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="TaskLabel">Task</h4>
                </div>
                <div class="modal-body">
                    <div class="col-xs-12">
                        <form id="task-form">
                            <input type="hidden" name="id" value="">
                            <div class="row">
                                <label class="control-label">Name</label>
                                <input type="text" class="form-control" name="name" value="">
                            </div>
                            <div class="row">
                                <label class="control-label">Description</label>
                                <textarea class="form-control" name="description"></textarea>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary saveTask">Save Task</button>
                    <button type="button" class="btn btn-danger deleteTask">Delete Task</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        var _$sortable = $( ".sortable" );
        _$sortable.sortable(
        {
            placeholder: "ui-state-highlight",
            connectWith: "div.sortable",
            update: onStop
        });
        _$sortable.disableSelection();

        $( '.saveTask' ).click(function () {
            var id = $('input[name="id"]').val();
            (id == '' ? addTask() : updateTask(id));
        });

        $( '.deleteTask' ).click(function () {
            var id = $('input[name="id"]').val();
            updateTaskManual(id, '', 'DELETE');
            $('#Task').modal('hide');
        });

        getTasks();

        function getTasks()
        {
            $.ajax({
                url: 'http://taskapi.johnumb.com/api/task/',
                dataType: 'JSON',
                type: 'GET',
                crossDomain: true
            }).done(function(response) {
                displayTasks(response);
            });
        }

        function displayTasks(response)
        {
            $('#available, #in-progress, #finished').children().remove();
            if (typeof response === 'object') {
                $.each(response, function(key, data)
                {
                    var _$container = $('#'+data['category']);
                    $( '.task-template' ).children( '.task' ).clone().appendTo(_$container);

                    _$container.children( ':last-child' ).children( '.task-heading' ).html(data['name']);
                    _$container.children( ':last-child' ).children( '.task-body-min' ).html(data['description']);
                    _$container.children( ':last-child' ).data(data);

                });
                $(document)
                    .off('click', '.task-heading, .task-body-min')
                    .on('click', '.task-heading, .task-body-min', editTask);
            }
        }

        function editTask()
        {
            var data = $(this).parent().data();
            $('input[name="id"]').val(data['id']);
            $('input[name="name"]').val(data['name']);
            $('textarea[name="description"]').val(data['description']);
            $('#Task').modal('show');
        }

        function addTask()
        {
            //TODO: Get very last order number.
            var data = $('#task-form').serialize();
            data += '&category=available&order=0';

            $.ajax({
                url: 'http://taskapi.johnumb.com/api/task/',
                dataType: 'JSON',
                type: 'POST',
                crossDomain: true,
                data: data
            }).done(function(response) {
                $('#Task').modal('hide');
                getTasks();
            });
        }

        function updateTask(id)
        {
            var data = $('#task-form').serialize();

            $.ajax({
                url: 'http://taskapi.johnumb.com/api/task/'+id,
                dataType: 'JSON',
                type: 'PUT',
                crossDomain: true,
                data: data
            }).done(function(response) {
                $('#Task').modal('hide');
                getTasks();
            });
        }

        function updateTaskManual(id, data, type)
        {
            $.ajax({
                url: 'http://taskapi.johnumb.com/api/task/'+id,
                dataType: 'JSON',
                type: type,
                crossDomain: true,
                data: data
            }).done(function(response) {
                getTasks();
            });
        }

        function onStop(event, ui)
        {
            var _$target = $(event.target);
            var data = ui.item.data();

            if (_$target.prop('id') != data['category']) {
                updateTaskManual(data['id'], 'id='+data['id']+'&category='+_$target.prop('id'), 'PUT');
            }

        }
    </script>
@endsection

