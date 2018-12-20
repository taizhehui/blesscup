@extends('layouts.cms.master')

@section('script')
    @include('layouts.cms.datatable_script')
    <script>
        $(function() {
            $('#data_table').DataTable({
                language: languageStrings,
                "ordering": false
            });

            $('#editModal').on('show.bs.modal', function (event) { 
                var button = $(event.relatedTarget);
                var folder = button.data('folder');
                var modal = $(this);
                var $form = modal.find('form');
                var $inputFolder = modal.find('#folder');
                var $inputFolder2 = modal.find('#folder2');

                var title = '{{ __('cms_folder.edit_folder_title') }}';
                modal.find('.modal-title').text(title);

                $inputFolder.val(folder.name);
                $inputFolder2.val(folder.display_name);

                $form.append('{{ method_field('PUT') }}');

                if(folder.parent_id!=undefined){ //this is a directory 
                    $form.attr('action', '/cms/folder/edit/directory/' + folder.id );
                }
                else{//this is file
                    $form.attr('action', '/cms/folder/edit/file/' + folder.id );
                }
            });

            $('#alertModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var folder= button.data('folder');
                var modal = $(this);

                modal.find('.modal-title').text('{{ __('cms_folder.alert_delete_title') }}' + " " + folder.name);
                modal.find('.modal-body').text('{{ __('cms_folder.alert_delete_body')[0] }}' + " " + folder.name + '{{ __('cms_folder.alert_delete_body')[1] }}');

                if(folder.parent_id!=undefined){ //if this is a directory (directory has parent id)
                    modal.find('.modal-footer form').attr('action', '/cms/folder/delete/directory/' + folder.id);
                }
                else{
                    modal.find('.modal-footer form').attr('action', '/cms/folder/delete/file/' + folder.id);
                }
            });

            $('#addModal').on('show.bs.modal', function (event) { 
                var button = $(event.relatedTarget);
                var current_directory = button.data('current');
                var modal = $(this);
                var $form = modal.find('form');

                var title = '{{ __('cms_folder.add_directory_title') }}';
                modal.find('.modal-title').text(title);

                $('input[name=_method]').remove();
                $form.attr('action', '/cms/folder/add/directory/'+current_directory);
            });

            $('#uploadModal').on('show.bs.modal', function (event) { 
                var button = $(event.relatedTarget);
                var current_directory = button.data('current');
                var modal = $(this);
                var $form = modal.find('form');

                var title = '{{ __('cms_folder.add_file_title') }}' ;
                modal.find('.modal-title').text(title);

                $('input[name=_method]').remove();
                $form.attr('action', '/cms/folder/add/file/'+current_directory);
            });

        });
    </script>
@endsection

@section('content')
    <h3>{{ __('cms_master.menu_folder') }}</h3>
    @include('layouts.cms.success')
    @include('layouts.cms.error_list', ['errors' => $errors])

    <a href="/cms/folder/2">{{ __('cms_language.chinese') }}</a>
    <a href="/cms/folder/3">{{ __('cms_language.english') }}</a>
    <a href="/cms/folder/4">{{ __('cms_language.japanese') }}</a>
    <a href="/cms/folder/5">{{ __('cms_language.nepali') }}</a>

    

    <div class="row">
        <div class="col text-right">
            <button class="btn btn-outline-dark" data-toggle="modal" data-target="#addModal" data-current="{{ $current_directory }}"><i class="fa fa-plus-square" style="margin-right:10px"></i><i class="fa fa-folder"></i></button>
            <button class="btn btn-outline-dark" data-toggle="modal" data-target="#uploadModal" data-current="{{ $current_directory }}"><i class="fa fa-upload" style="margin-right:10px"></i><i class="fa fa-file"></i></button>
        </div>
    </div>
    
    <div class="table_container table-responsive-lg">
        <table id="data_table" class="table table-hover table-md">
            <thead class="thead-light">
                <tr>
                    <th>{{ __('cms_folder.name') }}</th>
                    <th>{{ __('cms_folder.display_name') }}</th>
                    <th>{{ __('cms_folder.action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($directorylist as $directory)
                    <tr>
                        <td>
                            <a href=/cms/folder/{{ $directory->id }}><i id=material_icon class="fa fa-folder" style="margin-right:10px"></i>
                                {{ $directory->name }} 
                            </a>
                        </td>
                        <td>
                            {{ $directory->display_name}}
                        </td>

                        <td>
                            <button class="btn btn-success" data-toggle="modal" data-target="#editModal" data-folder="{{ $directory }}"><i class="fa fa-edit"></i></button>
                            <button class="btn btn-danger" data-toggle="modal" data-target="#alertModal" data-folder="{{ $directory}}"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                @endforeach

            
                @foreach ($filelist as $file)
                    <tr>
                        <td>
                            <i id=material_icon class="fa fa-file" style="margin-right:10px"></i>
                                {{ $file->local_filename}}
                        </td>
                        <td>
                            {{ $file->display_name}}
                        </td>

                        <td>
                            <button class="btn btn-success" data-toggle="modal" data-target="#editModal" data-folder="{{ $file }}"><i class="fa fa-edit"></i></button>
                            <button class="btn btn-danger" data-toggle="modal" data-target="#alertModal" data-folder="{{ $file }}"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>



    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="POST">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <label for="folder" class="col-form-label">{{ __('cms_folder.name') }}</label>
                        <input type="hidden" name="localeData[0][locale]" value="zh-HK">
                        <input class="form-control" id="folder" name="localeData[0][name]">

                        <label for="folder2" class="col-form-label">{{ __('cms_folder.display_name') }}</label>
                        <input type="hidden" name="localeData[0][locale]" value="zh-HK">
                        <input class="form-control" id="folder2" name="localeData[0][display_name]">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('cms_master.back') }}</button>
                        <button class="btn btn-success">{{ __('cms_master.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="POST">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <label for="folder" class="col-form-label">{{ __('cms_folder.name') }}</label>
                        <input type="hidden" name="localeData[0][locale]" value="zh-HK">
                        <input class="form-control" id="folder" name="localeData[0][name]">

                        <label for="folder2" class="col-form-label">{{ __('cms_folder.display_name') }}</label>
                        <input type="hidden" name="localeData[0][locale]" value="zh-HK">
                        <input class="form-control" id="folder2" name="localeData[0][display_name]">
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('cms_master.back') }}</button>
                        <button class="btn btn-success">{{ __('cms_master.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <label for="folder" class="col-form-label">{{ __('cms_folder.name') }}</label>
                        <input type="hidden" name="localeData[0][locale]" value="zh-HK">
                        <input class="form-control" id="folder" name="localeData[0][name]">

                        <label for="folder2" class="col-form-label">{{ __('cms_folder.display_name') }}</label>
                        <input type="hidden" name="localeData[0][locale]" value="zh-HK">
                        <input class="form-control" id="folder2" name="localeData[0][display_name]">

                        <label for="folder3" class="col-form-label">{{ __('cms_folder.mp3') }}</label><br>
                        <input type="file" name="mp3" value="">
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('cms_master.back') }}</button>
                        <button class="btn btn-success">{{ __('cms_master.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('layouts.cms.alert_modal')
@endsection


