@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Título --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Dashboard</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contenido según el rol --}}
        <div class="row">
            <div class="col-lg-12">
                @if($view === 'agent')
                    @include('dashboard.partials.agent')
                @elseif($view === 'admin')
                    @include('dashboard.partials.admin')
                @else
                    @include('dashboard.partials.client')
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    @stack('dashboard-scripts')
@endpush


