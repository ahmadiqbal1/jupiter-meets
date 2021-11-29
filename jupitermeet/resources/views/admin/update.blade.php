@extends('layouts.admin')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
<div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-sm-6">
          <div class="row">
            <div class="form-group">
              <span>Current version: </span>
              <label>{{ getSetting('VERSION') }}</label>
            </div>
          </div>
          <div class="row">
            <div class="form-group">
              <button id="checkForUpdate" class="btn btn-primary">Check for update</button>
              <button id="downloadUpdate" class="btn btn-success" hidden>Download</button>
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
              <div class="callout callout-info">
                <h5>Changelog</h5>

                <pre id="changelog">-</pre>
              </div>
            </div>
        </div>
      </div>
    </div>
</div>
@endsection
