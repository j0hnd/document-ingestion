@extends('layouts.default')

@section('title')
    @parent
    {{ $title }}
@stop

@section('css')
@stop

@section('content')
    @include('layouts.partials.menu', ['page' => 'queue'])

    <div id="main" class="uk-container uk-container-center">
        <table class="uk-table uk-table-hover uk-table-striped">
            <thead>
            <tr>
                <th>Purchase Order</th>
                <th>Status</th>
                <th>Reviewed By</th>
                <th>Date Created</th>
            </tr>
            </thead>
            <tbody id="table-body-queue">
            @include('Documents::partials.po-list', ['po' => $documents])
            </tbody>
        </table>
    </div>
@stop

@section('js')

@stop