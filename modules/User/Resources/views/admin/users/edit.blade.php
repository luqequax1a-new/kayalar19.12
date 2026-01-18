@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('user::users.user')]))
    @slot('subtitle', $user->full_name)

    <li><a href="{{ route('admin.users.index') }}">{{ trans('user::users.users') }}</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('user::users.user')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="form-horizontal" id="user-edit-form" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}

        {!! $tabs->render(compact('user')) !!}
    </form>

    <div class="customer-insights-section">
        <div class="section-title-wrapper" style="margin: 40px 0 25px 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center;">
                <div class="premium-icon-box" style="background: #6366f1; color: #fff; margin-right: 12px; width: 40px; height: 40px; font-size: 20px; box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);">
                    <i class="fa fa-pie-chart"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-weight: 800; color: #0f172a; font-size: 20px; letter-spacing: -0.5px;">Müşteri Paneli</h3>
                    <p style="margin: 0; color: #64748b; font-size: 13px;">Müşterinin ticari ve iletişim geçmişine dair detaylı özet.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @include('user::admin.users.tabs.orders')
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @include('user::admin.users.tabs.top_products')
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                @include('user::admin.users.tabs.coupons')
            </div>
            <div class="col-md-6">
                @include('user::admin.users.tabs.emails')
            </div>
        </div>
    </div>
@endsection

@push('globals')
    <style>
        .customer-insights-section {
            padding-top: 20px;
            max-width: 100%;
        }
        .premium-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.02);
            border: 1px solid #f1f5f9;
            margin-bottom: 24px;
            overflow: hidden;
        }
        .premium-card-header {
            padding: 16px 20px;
            background: #fff;
            border-bottom: 1px solid #f8fafc;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .premium-card-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }
        .premium-icon-box {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }
        .premium-table {
            table-layout: fixed;
            width: 100%;
            margin-bottom: 0 !important;
        }
        .premium-table thead th {
            background-color: #fafafa;
            border-bottom: 1px solid #f1f5f9 !important;
            font-weight: 700;
            padding: 12px 15px !important;
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            vertical-align: middle;
        }
        .premium-table tbody td {
            padding: 14px 15px !important;
            vertical-align: middle;
            border-bottom: 1px solid #f8fafc;
            font-size: 13px;
            color: #334155;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .premium-row:hover {
            background-color: #fcfdfe !important;
        }
        .badge-premium {
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }
        .insight-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            border: 1px solid #e2e8f0;
        }
        .text-mono {
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: -0.2px;
        }
        .progress-compact-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            max-width: 160px;
        }
        .progress-compact {
            flex-grow: 1;
            height: 6px;
            background: #f1f5f9;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        .progress-compact-bar {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            transition: width 0.4s ease;
        }
    </style>
@endpush

@include('user::admin.users.partials.shortcuts')

@push('globals')
    @vite([
        'modules/User/Resources/assets/admin/sass/main.scss',
        'modules/User/Resources/assets/admin/js/main.js'
    ])
@endpush
