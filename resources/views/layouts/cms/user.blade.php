@extends('layouts.cms.master')

@section('script')
    @include('layouts.cms.datatable_script')
    <script>
        $(function() {
            $('#data_table').DataTable({
                language: languageStrings,
                // columnDefs: [
                //     {
                //         targets: 6,
                //         orderable: false
                //     }
                // ]
            });
        });
    </script>
@endsection

@section('content')
    <h3>{{ __('cms_master.menu_user_list') }}</h3>
    @include('layouts.cms.success')
    <a href="/cms/user/1">{{ __('cms_language.chinese') }}</a>
    <a href="/cms/user/2">{{ __('cms_language.english') }}</a>
    <a href="/cms/user/3">{{ __('cms_language.japanese') }}</a>
    <a href="/cms/user/4">{{ __('cms_language.nepali') }}</a>
    
    <div class="table_container table-responsive-lg">
        <table id="data_table" class="table table-hover table-md">
            <thead class="thead-light">
            <tr>
                <th>{{ __('cms_user.cup_id') }}</th>
                <th>{{ __('cms_user.language') }}</th>
                <th>{{ __('cms_user.name') }}</th>
                <th>{{ __('cms_user.email') }}</th>
                <th>{{ __('cms_user.gender') }}</th>
                <th>{{ __('cms_user.dob') }}</th>
                <th>{{ __('cms_user.register_date') }}</th>
            </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->uuid }}</td>
                        <td>{{ $user->language}}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->gender }}</td>
                        <td>{{ $user->dob }}</td>
                        <td>{{ $user->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

