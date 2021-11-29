@extends('layouts.admin')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
<div class="card">
    <div class="card-body">
      <table id="globalConfig" class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
          <th>ID</th>
          <th>Key</th>
          <th>Value</th>
          <th>Description</th>
          <th>Action</th>
        </tr>
        </thead>
        <tbody>
          @foreach ($data as $key => $value)
          <tr>
            <td>{{ $value->id }}</td>
            <td>{{ $value->key }}</td>
            @if($value->key == 'PRIMARY_COLOR' || $value->key == 'PRIMARY_COLOR_DISABLED' || $value->key == 'SECONDARY_COLOR')
              <td style="max-width: 250px">
                <div class="color-palette" style="background-color: {{ $value->value }}">
                  <span>{{ $value->value }}</span>
                </div>
              </td>
            @else
              <td style="max-width: 250px">{{ $value->value }}</td>
            @endif
            <td>{{ $value->description }}</td>
            <td>
                <a href="/global-config/edit/{{ $value->id }}">
                  <button class="btn btn-primary btn-sm">
                    <i class="fa fa-edit"></i>
                  </button>
                </a>
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
        <tr>
          <th>ID</th>
          <th>Key</th>
          <th>Value</th>
          <th>Description</th>
          <th>Action</th>
        </tr>
        </tfoot>
      </table>
    </div>
  </div>
@endsection
