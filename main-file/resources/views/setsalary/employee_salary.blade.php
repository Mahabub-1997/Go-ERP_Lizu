@extends('layouts.admin')
@section('page-title')
    {{ __('Employee Set Salary') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employee.index') }}">{{ __('Employee') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Set Salary') }}</li>
@endsection
@section('content')
    @if (!empty($employee))
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card min-height-253">
                            <div class="card-header d-flex align-items-center gap-2 flex-wrap justify-content-between">
                                <h5 class="mb-0">{{ __('Employee Salary') }}</h5>
                                @can('create set salary')
                                    <div class="col text-end">
                                        <a href="#" data-url="{{ route('employee.basic.salary', $employee->id) }}"
                                            data-size="md" data-ajax-popup="true" data-title="{{ __('Set Basic Sallary') }}"
                                            data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="ti ti-plus"></i>
                                        </a>
                                    </div>
                                @endcan
                            </div>
                            <div class="card-body table-border-style full-card">
                                <div class="project-info d-flex">
                                    <div class="project-info-inner mr-3 col-4">
                                        <b class="m-0"> {{ __('Payslip Type') }} </b>
                                        <div class="project-amnt pt-1">
                                            @if (!empty($employee->salaryType))
                                                {{ $employee->salaryType->name }}
                                            @else
                                                --
                                            @endif
                                        </div>
                                    </div>
                                    <div class="project-info-inner mr-3 col-4">
                                        <b class="m-0"> {{ __('Salary') }} </b>
                                        <div class="project-amnt pt-1">
                                            @if (!empty($employee->salary))
                                                {{ $employee->salary }}
                                            @else
                                                --
                                            @endif
                                        </div>
                                    </div>
                                    <div class="project-info-inner mr-3 col-4">
                                        <b class="m-0"> {{ __('Account') }} </b>
                                        <div class="project-amnt pt-1">
                                            @if (!empty($employee->bankAccount))
                                                {{ $employee->bankAccount->bank_name }}
                                            @else
                                                --
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card min-height-253">

                            <div class="card-header d-flex align-items-center gap-2 flex-wrap justify-content-between ">
                                        <h5 class="mb-0">{{ __('Allowance') }}</h5>
                                    @can('create allowance')
                                            <a href="#!" data-url="{{ route('allowances.create', $employee->id) }}"
                                                data-size="lg" data-ajax-popup="true" data-title="{{ __('Create Allowance') }}"
                                                data-bs-toggle="tooltip" title="{{ __('Create') }}"
                                                data-original-title="{{ __('Create Allowance') }}"
                                                class="apply-btn btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                    @endcan
                            </div>
                            <div class="card-body table-border-style full-card">
                                <div class="table-responsive">
                                    @if (!$allowances->isEmpty())
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Employee Name') }}</th>
                                                    <th>{{ __('Allownace Option') }}</th>
                                                    <th>{{ __('Title') }}</th>
                                                    <th>{{ __('Type') }}</th>
                                                    <th>{{ __('Amount') }}</th>
                                                    @if (\Auth::user()->type != 'Employee')
                                                        <th>{{ __('Action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($allowances as $allowance)
                                                    <tr>
                                                        <td>{{ $employee->name }}</td>
                                                        <td>{{ !empty($allowance->allowanceOption) ? $allowance->allowanceOption->name : '' }}
                                                        </td>
                                                        <td>{{ $allowance->title }}</td>

                                                        <td>{{ ucfirst($allowance->type) }}</td>
                                                        @if ($allowance->type == 'fixed')
                                                            <td>{{ \Auth::user()->priceFormat($allowance->amount) }}</td>
                                                        @else
                                                            <td>{{ $allowance->amount }}%
                                                                (${{ $allowance->tota_allow }})</td>
                                                        @endif
                                                        @if (\Auth::user()->type != 'Employee')
                                                            <td class="">
                                                                @can('edit allowance')
                                                                    <div class="action-btn me-2">
                                                                        <a href="#!"
                                                                            data-url="{{ URL::to('allowance/' . $allowance->id . '/edit') }}"
                                                                            data-size="lg" data-ajax-popup="true"
                                                                            data-title="{{ __('Edit Allowance') }}"
                                                                            class="mx-3 btn btn-sm align-items-center bg-info"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Edit') }}"
                                                                            data-original-title="{{ __('Edit') }}"><i
                                                                                class="ti ti-pencil text-white"></i></a>
                                                                    </div>
                                                                @endcan
                                                                @can('delete allowance')
                                                                    <div class="action-btn ">
                                                                        {!! Form::open([
                                                                            'method' => 'DELETE',
                                                                            'route' => ['allowance.destroy', $allowance->id],
                                                                            'id' => 'allowance-delete-form-' . $allowance->id,
                                                                        ]) !!}
                                                                        <a href="#!"
                                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Delete') }}"
                                                                            data-original-title="{{ __('Delete') }}"
                                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                            data-confirm-yes="document.getElementById('allowance-delete-form-{{ $allowance->id }}').submit();"><i
                                                                                class="ti ti-trash text-white"></i></a>
                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                @endcan
                                                            </td>
                                                        @endif

                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="mt-2 text-center">
                                            No Allowance Found!
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card  min-height-253">
                            <div class="card-header d-flex align-items-center gap-2 flex-wrap justify-content-between">
                                        <h5 class="mb-0">{{ __('Commission') }}</h5>
                                    @can('create commission')
                                            <a href="#!" data-url="{{ route('commissions.create', $employee->id) }}"
                                                data-size="lg" data-ajax-popup="true"
                                                data-title="{{ __('Create Commission') }}" data-bs-toggle="tooltip"
                                                title="{{ __('Create') }}"
                                                data-original-title="{{ __('Create Commission') }}"
                                                class="apply-btn btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                    @endcan
                            </div>
                            <div class="card-body table-border-style full-card">
                                <div class="table-responsive">
                                    @if (!$commissions->isEmpty())
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Employee Name') }}</th>
                                                    <th>{{ __('Title') }}</th>
                                                    <th>{{ __('Type') }}</th>
                                                    <th>{{ __('Amount') }}</th>
                                                    @if (\Auth::user()->type != 'Employee')
                                                        <th>{{ __('Action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($commissions as $commission)
                                                    <tr>
                                                        <td>{{ $employee->name }}</td>
                                                        <td>{{ $commission->title }}</td>
                                                        <td>{{ ucfirst($commission->type) }}</td>

                                                        @if ($commission->type == 'fixed')
                                                            <td>{{ \Auth::user()->priceFormat($commission->amount) }}</td>
                                                        @else
                                                            <td>{{ $commission->amount }}%
                                                                ({{ \Auth::user()->priceFormat($commission->tota_allow) }})
                                                            </td>
                                                        @endif
                                                        @if (\Auth::user()->type != 'Employee')
                                                            <td class="">
                                                                @can('edit commission')
                                                                    <div class="action-btn me-2">
                                                                        <a href="#!"
                                                                            data-url="{{ URL::to('commission/' . $commission->id . '/edit') }}"
                                                                            data-size="lg" data-ajax-popup="true"
                                                                            data-title="{{ __('Edit Commission') }}"
                                                                            class="mx-3 btn btn-sm align-items-center bg-info"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Edit') }}"
                                                                            data-original-title="{{ __('Edit') }}"><i
                                                                                class="ti ti-pencil text-white"></i></a>
                                                                    </div>
                                                                @endcan
                                                                @can('delete commission')
                                                                    <div class="action-btn ">
                                                                        {!! Form::open([
                                                                            'method' => 'DELETE',
                                                                            'route' => ['commission.destroy', $commission->id],
                                                                            'id' => 'commission-delete-form-' . $commission->id,
                                                                        ]) !!}

                                                                        <a href="#!"
                                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Delete') }}"
                                                                            data-original-title="{{ __('Delete') }}"
                                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                            data-confirm-yes="document.getElementById('commission-delete-form-{{ $commission->id }}').submit();"><i
                                                                                class="ti ti-trash text-white"></i></a>
                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                @endcan
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="mt-2 text-center">
                                            No Commission Found!
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card min-height-253">
                            <div class="card-header d-flex align-items-center gap-2 flex-wrap justify-content-between">

                                        <h5 class="mb-0">{{ __('Loan') }}</h5>
                                    @can('create loan')
                                            <a href="#!" data-url="{{ route('loans.create', $employee->id) }}"
                                                data-size="lg" data-ajax-popup="true" data-title="{{ __('Create Loan') }}"
                                                data-bs-toggle="tooltip" title="{{ __('Create') }}"
                                                data-original-title="{{ __('Create Loan') }}"
                                                class="apply-btn btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                    @endcan
                            </div>
                            <div class="card-body table-border-style full-card">
                                <div class="table-responsive">
                                    @if (!$loans->isEmpty())
                                        <table class="table table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Employee') }}</th>
                                                    <th>{{ __('Loan Options') }}</th>
                                                    <th>{{ __('Title') }}</th>
                                                    <th>{{ __('Type') }}</th>
                                                    <th>{{ __('Loan Amount') }}</th>
                                                    @if (\Auth::user()->type != 'Employee')
                                                        <th>{{ __('Action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($loans as $loan)
                                                    <tr>
                                                        <td>{{ $employee->name }}</td>
                                                        <td>{{ !empty($loan->loanOption) ? $loan->loanOption->name : '' }}
                                                        </td>
                                                        <td>{{ $loan->title }}</td>
                                                        <td>{{ ucfirst($loan->type) }}</td>
                                                        @if ($loan->type == 'fixed')
                                                            <td>{{ \Auth::user()->priceFormat($loan->amount) }}</td>
                                                        @else
                                                            <td>{{ $loan->amount }}% (${{ $loan->tota_allow }})</td>
                                                        @endif
                                                        @if (\Auth::user()->type != 'Employee')
                                                            <td class="">
                                                                @can('edit loan')
                                                                    <div class="action-btn me-2">
                                                                        <a href="#!"
                                                                            data-url="{{ URL::to('loan/' . $loan->id . '/edit') }}"
                                                                            data-size="lg" data-ajax-popup="true"
                                                                            data-title="{{ __('Edit Loan') }}"
                                                                            class="mx-3 btn btn-sm align-items-center bg-info"
                                                                            title="{{ __('Edit') }}"
                                                                            data-bs-toggle="tooltip"
                                                                            data-original-title="{{ __('Edit') }}"><i
                                                                                class="ti ti-pencil text-white"></i></a>
                                                                    </div>
                                                                @endcan
                                                                @can('delete loan')
                                                                    <div class="action-btn me-2">
                                                                        {!! Form::open([
                                                                            'method' => 'DELETE',
                                                                            'route' => ['loan.destroy', $loan->id],
                                                                            'id' => 'loan-delete-form-' . $loan->id,
                                                                        ]) !!}

                                                                        <a href="#!"
                                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Delete') }}"
                                                                            data-original-title="{{ __('Delete') }}"
                                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                            data-confirm-yes="document.getElementById('loan-delete-form-{{ $loan->id }}').submit();"><i
                                                                                class="ti ti-trash text-white"></i></a>
                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                @endcan
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="mt-2 text-center">
                                            No Loan Data Found!
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card min-height-253">
                            <div class="card-header d-flex align-items-center gap-2 flex-wrap justify-content-between">
                                        <h5 class="mb-0">{{ __('Saturation Deduction') }}</h5>
                                    @can('create saturation deduction')
                                            <a href="#!"
                                                data-url="{{ route('saturationdeductions.create', $employee->id) }}"
                                                data-size="lg" data-ajax-popup="true"
                                                data-title="{{ __('Create Saturation Deduction') }}" data-bs-toggle="tooltip"
                                                title="{{ __('Create') }}"
                                                data-original-title="{{ __('Create Saturation Deduction') }}"
                                                class="apply-btn btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                    @endcan
                            </div>
                            <div class="card-body table-border-style full-card">

                                <div class="table-responsive">
                                    @if (!$saturationdeductions->isEmpty())
                                        <table class="table table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Employee Name') }}</th>
                                                    <th>{{ __('Deduction Option') }}</th>
                                                    <th>{{ __('Title') }}</th>
                                                    <th>{{ __('Type') }}</th>
                                                    <th>{{ __('Amount') }}</th>
                                                    @if (\Auth::user()->type != 'Employee')
                                                        <th>{{ __('Action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($saturationdeductions as $saturationdeduction)
                                                    <tr>
                                                        <td>{{ $employee->name }}</td>
                                                        <td>{{ !empty($saturationdeduction->deductionOption) ? $saturationdeduction->deductionOption->name : '' }}
                                                        </td>
                                                        <td>{{ $saturationdeduction->title }}</td>
                                                        <td>{{ ucfirst($saturationdeduction->type) }}</td>
                                                        @if ($saturationdeduction->type == 'fixed')
                                                            <td>{{ \Auth::user()->priceFormat($saturationdeduction->amount) }}
                                                            </td>
                                                        @else
                                                            <td>{{ $saturationdeduction->amount }}%
                                                                (${{ $saturationdeduction->tota_allow }})</td>
                                                        @endif
                                                        @if (\Auth::user()->type != 'Employee')
                                                            <td class="">
                                                                @can('edit saturation deduction')
                                                                    <div class="action-btn me-2">
                                                                        <a href="#!"
                                                                            data-url="{{ URL::to('saturationdeduction/' . $saturationdeduction->id . '/edit') }}"
                                                                            data-size="lg" data-ajax-popup="true"
                                                                            data-title="{{ __('Edit Saturation Deduction') }}"
                                                                            class="mx-3 btn btn-sm align-items-center bg-info"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Edit') }}"
                                                                            data-original-title="{{ __('Edit') }}"><i
                                                                                class="ti ti-pencil text-white"></i></a>
                                                                    </div>
                                                                @endcan
                                                                @can('delete saturation deduction')
                                                                    <div class="action-btn ">
                                                                        {!! Form::open([
                                                                            'method' => 'DELETE',
                                                                            'route' => ['saturationdeduction.destroy', $saturationdeduction->id],
                                                                            'id' => 'deduction-delete-form-' . $saturationdeduction->id,
                                                                        ]) !!}

                                                                        <a href="#!"
                                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Delete') }}"
                                                                            data-original-title="{{ __('Delete') }}"
                                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                            data-confirm-yes="document.getElementById('deduction-delete-form-{{ $saturationdeduction->id }}').submit();"><i
                                                                                class="ti ti-trash text-white"></i></a>
                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                @endcan
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="mt-2 text-center">
                                            No Saturation Deduction Found!
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card min-height-253">
                            <div class="card-header d-flex align-items-center gap-2 flex-wrap justify-content-between">
                                        <h5 class="mb-0">{{ __('Other Payment') }}</h5>
                                    @can('create other payment')
                                            <a href="#!" data-url="{{ route('otherpayments.create', $employee->id) }}"
                                                data-size="lg" data-ajax-popup="true"
                                                data-title="{{ __('Create Other Payment') }}" data-bs-toggle="tooltip"
                                                title="{{ __('Create') }}"
                                                data-original-title="{{ __('Create Other Payment') }}"
                                                class="apply-btn btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                    @endcan
                            </div>
                            <div class="card-body table-border-style full-card">
                                <div class="table-responsive">
                                    @if (!$otherpayments->isEmpty())
                                        <table class="table table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Employee') }}</th>
                                                    <th>{{ __('Title') }}</th>
                                                    <th>{{ __('Type') }}</th>
                                                    <th>{{ __('Amount') }}</th>
                                                    @if (\Auth::user()->type != 'Employee')
                                                        <th>{{ __('Action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($otherpayments as $otherpayment)
                                                    <tr>
                                                        <td>{{ $employee->name }}</td>
                                                        <td>{{ $otherpayment->title }}</td>
                                                        <td>{{ ucfirst($otherpayment->type) }}</td>
                                                        @if ($otherpayment->type == 'fixed')
                                                            <td>{{ \Auth::user()->priceFormat($otherpayment->amount) }}
                                                            </td>
                                                        @else
                                                            <td>{{ $otherpayment->amount }}%
                                                                (${{ $otherpayment->tota_allow }})</td>
                                                        @endif
                                                        @if (\Auth::user()->type != 'Employee')
                                                            <td class="">
                                                                @can('edit other payment')
                                                                    <div class="action-btn me-2">
                                                                        <a href="#!"
                                                                            data-url="{{ URL::to('otherpayment/' . $otherpayment->id . '/edit') }}"
                                                                            data-size="lg" data-ajax-popup="true"
                                                                            title="{{ __('Edit') }}"
                                                                            data-title="{{ __('Edit Other Payment') }}"
                                                                            class="mx-3 btn btn-sm  align-items-center bg-info"
                                                                            data-bs-toggle="tooltip"
                                                                            data-original-title="{{ __('Edit') }}"><i
                                                                                class="ti ti-pencil text-white"></i></a>
                                                                    </div>
                                                                @endcan

                                                                @can('delete other payment')
                                                                    <div class="action-btn ">
                                                                        {!! Form::open([
                                                                            'method' => 'DELETE',
                                                                            'route' => ['otherpayment.destroy', $otherpayment->id],
                                                                            'id' => 'payment-delete-form-' . $otherpayment->id,
                                                                        ]) !!}
                                                                        <a href="#!"
                                                                            class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-danger"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Delete') }}"
                                                                            data-original-title="{{ __('Delete') }}"
                                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                            data-confirm-yes="document.getElementById('payment-delete-form-{{ $otherpayment->id }}').submit();"><i
                                                                                class="ti ti-trash text-white"></i></a>
                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                @endcan
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="mt-2 text-center">
                                            No Other Payment Data Found!
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header d-flex align-items-center gap-2 flex-wrap justify-content-between">
                                        <h5 class="mb-0">{{ __('Overtime') }}</h5>
                                    @can('create overtime')
                                            <a href="#!" data-url="{{ route('overtimes.create', $employee->id) }}"
                                                data-size="lg" data-ajax-popup="true"
                                                data-title="{{ __('Create Overtime') }}" data-bs-toggle="tooltip"
                                                data-bs-original-title="{{ __('Create') }}"
                                                class="apply-btn btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                    @endcan
                            </div>
                            <div class="card-body table-border-style full-card">
                                <div class="table-responsive">
                                    @if (!$overtimes->isEmpty())
                                        <table class="table table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Employee Name') }}</th>
                                                    <th>{{ __('Overtime Title') }}</th>
                                                    <th>{{ __('Number of days') }}</th>
                                                    <th>{{ __('Hours') }}</th>
                                                    <th>{{ __('Rate') }}</th>
                                                    @if (\Auth::user()->type != 'Employee')
                                                        <th>{{ __('Action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($overtimes as $overtime)
                                                    <tr>
                                                        <td>{{ $employee->name }}</td>
                                                        <td>{{ $overtime->title }}</td>
                                                        <td>{{ $overtime->number_of_days }}</td>
                                                        <td>{{ $overtime->hours }}</td>
                                                        <td>{{ \Auth::user()->priceFormat($overtime->rate) }}</td>
                                                        @if (\Auth::user()->type != 'Employee')
                                                            <td class="">
                                                                @can('edit overtime')
                                                                    <div class="action-btn me-2">

                                                                        <a href="#!"
                                                                            data-url="{{ URL::to('overtime/' . $overtime->id . '/edit') }}"
                                                                            data-size="lg" data-ajax-popup="true"
                                                                            data-title="{{ __('Edit OverTime') }}"
                                                                            class="mx-3 btn btn-sm align-items-center bg-info"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Edit') }}"
                                                                            data-original-title="{{ __('Edit') }}"><i
                                                                                class="ti ti-pencil text-white"></i></a>
                                                                    </div>
                                                                @endcan
                                                                @can('delete overtime')
                                                                    <div class="action-btn ">
                                                                        {!! Form::open([
                                                                            'method' => 'DELETE',
                                                                            'route' => ['overtime.destroy', $overtime->id],
                                                                            'id' => 'overtime-delete-form-' . $overtime->id,
                                                                        ]) !!}

                                                                        <a href="#!"
                                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger"
                                                                            data-bs-toggle="tooltip"
                                                                            title="{{ __('Delete') }}"
                                                                            data-original-title="{{ __('Delete') }}"
                                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                            data-confirm-yes="document.getElementById('overtime-delete-form-{{ $overtime->id }}').submit();"><i
                                                                                class="ti ti-trash text-white"></i></a>
                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                @endcan
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="mt-2 text-center">
                                            No Overtime Data Found!
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif
@endsection

@push('script-page')
    @if (!empty($employee))
        <script type="text/javascript">
            $(document).on('change', '.amount_type', function() {

                var val = $(this).val();
                var label_text = 'Amount';
                if (val == 'percentage') {
                    var label_text = 'Percentage';
                }
                $('.amount_label').html(label_text);
            });


            $(document).ready(function() {
                var d_id = $('#department_id').val();
                var designation_id = '{{ $employee->designation_id }}';
                getDesignation(d_id);


                $("#allowance-dataTable").dataTable({
                    "columnDefs": [{
                        "sortable": false,
                        "targets": [1]
                    }]
                });

                $("#commission-dataTable").dataTable({
                    "columnDefs": [{
                        "sortable": false,
                        "targets": [1]
                    }]
                });

                $("#loan-dataTable").dataTable({
                    "columnDefs": [{
                        "sortable": false,
                        "targets": [1]
                    }]
                });

                $("#saturation-deduction-dataTable").dataTable({
                    "columnDefs": [{
                        "sortable": false,
                        "targets": [1]
                    }]
                });

                $("#other-payment-dataTable").dataTable({
                    "columnDefs": [{
                        "sortable": false,
                        "targets": [1]
                    }]
                });

                $("#overtime-dataTable").dataTable({
                    "columnDefs": [{
                        "sortable": false,
                        "targets": [1]
                    }]
                });
            });

            $(document).on('change', 'select[name=department_id]', function() {
                var department_id = $(this).val();
                getDesignation(department_id);
            });

            function getDesignation(did) {
                $.ajax({
                    url: '{{ route('employee.json') }}',
                    type: 'POST',
                    data: {
                        "department_id": did,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        $('#designation_id').empty();
                        $('#designation_id').append(
                            '<option value="">{{ __('Select any Designation') }}</option>');
                        $.each(data, function(key, value) {
                            var select = '';
                            if (key == '{{ $employee->designation_id }}') {
                                select = 'selected';
                            }

                            $('#designation_id').append('<option value="' + key + '"  ' + select + '>' +
                                value + '</option>');
                        });
                    }
                });
            }
        </script>
    @endif
@endpush
