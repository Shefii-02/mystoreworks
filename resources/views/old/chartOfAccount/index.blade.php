@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Chart of Accounts') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Chart of Account') }}</li>
@endsection
@push('script-page')
    <script>
        $(document).on('change', '#sub_type', function() {
            $('.acc_check').removeClass('d-none');
            var type = $(this).val();
            $.ajax({
                url: '{{ route('charofAccount.subType') }}',
                type: 'POST',
                data: {
                    "type": type,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#parent').empty();
                    $.each(data, function(key, value) {
                        console.log(key, value);
                        $('#parent').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                }
            });
        });
        $(document).on('click', '#account', function() {
            const element = $('#account').is(':checked');
            $('.acc_type').addClass('d-none');
            if (element == true) {
                $('.acc_type').removeClass('d-none');
            } else {
                $('.acc_type').addClass('d-none');
            }
        });
    </script>
@endpush

@section('action-btn')
    <div class="d-flex">
        @can('create chart of account')
            <a href="#" data-url="{{ route('chart-of-account.create') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
                data-size="lg" data-ajax-popup="true" data-title="{{ __('Create New Account') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="mt-2" id="multiCollapseExample1">
            <div class="card" id="show_filter">
                <div class="card-body">
                    {{ Form::open(['route' => ['chart-of-account.index'], 'method' => 'GET', 'id' => 'report_bill_summary']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('start_date', $filter['startDateRange'], ['class' => 'startDate form-control']) }}
                                    </div>
                                </div>

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('end_date', $filter['endDateRange'], ['class' => 'endDate form-control']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto d-flex mt-4">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="#" class="btn btn-sm btn-primary me-2"
                                        onclick="document.getElementById('report_bill_summary').submit(); return false;"
                                        data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                        data-original-title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>

                                    <a href="{{ route('chart-of-account.index') }}" class="btn btn-sm btn-danger "
                                        data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                        data-original-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i
                                                class="ti ti-refresh text-white-off"></i></span>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
    <div class="row">
        {{-- @dd($chartAccounts); --}}
        @foreach ($chartAccounts as $type => $accounts)
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6>{{ $type }}</h6>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th> {{ __('Code') }}</th>
                                        <th> {{ __('Name') }}</th>
                                        <th> {{ __('Type') }}</th>
                                        <th width="20%"> {{ __('Parent Account Name') }}</th>
                                        <th> {{ __('Balance') }}</th>
                                        <th> {{ __('Status') }}</th>
                                        <th width="10%"> {{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($accounts as $account)
                                        @php
                                            $balance = 0;
                                            $totalDebit = 0;
                                            $totalCredit = 0;
                                            $totalBalance = App\Models\Utility::getAccountBalance($account->id, $filter['startDateRange'], $filter['endDateRange']);
                                        @endphp

                                        <tr>
                                            <td>{{ $account->code }}</td>
                                            <td><a
                                                    href="{{ route('report.ledger') }}?account={{ $account->id }}">{{ $account->name }}</a>
                                            </td>
                                            <td>{{ !empty($account->subType) ? $account->subType->name : '-' }}</td>
                                            <td>{{ !empty($account->parentAccount) ? $account->parentAccount->name : '-' }}
                                            <td>
                                                @if (!empty($totalBalance))
                                                    {{ \Auth::user()->priceFormat($totalBalance) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($account->is_enabled == 1)
                                                    <span
                                                        class="badge bg-success p-2 px-3 ">{{ __('Enabled') }}</span>
                                                @else
                                                    <span
                                                        class="badge bg-danger p-2 px-3 ">{{ __('Disabled') }}</span>
                                                @endif
                                            </td>
                                            <td class="Action">
                                                <div class="action-btn me-2">
                                                    <a href="{{ route('report.ledger') }}?account={{ $account->id }}"
                                                        class="mx-3 btn btn-sm align-items-center bg-warning" data-bs-toggle="tooltip"
                                                        title="{{ __('Transaction Summary') }}"
                                                        data-original-title="{{ __('Ledger Summary') }}">
                                                        <i class="ti ti-wave-sine text-white"></i>
                                                    </a>
                                                </div>
                                                @can('edit chart of account')
                                                    <div class="action-btn me-2">
                                                        <a class="mx-3 btn btn-sm align-items-center bg-info"
                                                            data-url="{{ route('chart-of-account.edit', $account->id) }}"
                                                            data-ajax-popup="true" data-title="{{ __('Edit Account') }}"
                                                            data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                            data-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete chart of account')
                                                    <div class="action-btn">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['chart-of-account.destroy', $account->id],
                                                            'id' => 'delete-form-' . $account->id,
                                                        ]) !!}
                                                        <a href="#"
                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger"
                                                            data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                            data-original-title="{{ __('Delete') }}"
                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $account->id }}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
