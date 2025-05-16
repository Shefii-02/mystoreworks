@extends('layouts.admin')
@push('script-page')
    <script type="text/javascript">
        $(document).on("click", ".email-template-checkbox", function() {
            var chbox = $(this);
            $.ajax({
                url: chbox.attr('data-url'),
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    status: chbox.val()
                },
                type: 'post',
                success: function(response) {
                    if (response.is_success) {
                        toastr('Success', response.success, 'success');
                        if (chbox.val() == 1) {
                            $('#' + chbox.attr('id')).val(0);
                        } else {
                            $('#' + chbox.attr('id')).val(1);
                        }
                    } else {
                        toastr('Error', response.error, 'error');
                    }
                },
                error: function(response) {
                    response = response.responseJSON;
                    if (response.is_success) {
                        toastr('Error', response.error, 'error');
                    } else {
                        toastr('Error', response, 'error');
                    }
                }
            })
        });
    </script>
@endpush
@section('page-title')
    {{ __('Manage Email Templates') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Email Templates') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>

    <li class="breadcrumb-item active" aria-current="page">{{ __('Email Template') }}</li>
@endsection
@section('action-btn')
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5></h5>
                    <div class="table-responsive">
                        <table class="table datatable" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th scope="col" class="sort" data-sort="section"> {{ __('Section') }}</th>
                                    <th scope="col" class="sort" data-sort="name"> {{ __('Name') }}</th>
                                    <th class="text-end">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($EmailTemplates as $EmailTemplate)
                                    <tr>
                                        <td>{{ $EmailTemplate->from }}</td>
                                        <td>{{ $EmailTemplate->name }}</td>
                                        <td>
                                            <div class="dt-buttons">
                                                <div class="text-end">
                                                    <div class="action-btn">
                                                        <a href="{{ route('admin.manage.email.language', [$EmailTemplate->id, \Auth::user()->lang]) }}"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center  bg-warning"
                                                            data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                            <span class="text-white"><i class="ti ti-eye"></i></span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
