@extends('layouts.admin')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('style')
<link href="{{ asset('css/summernote-bs4.min.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="card">
    <div class="card-body">
      <form id="contentEdit">
        <div class="row">
          <div class="col-sm-12">
            <div class="form-group">
              <label>{{ $model->key }}</label>
              <input type="hidden" id="id" value="{{ $model->id }}">
                <textarea id="content" class="form-control">{{ $model->value }}</textarea>
            </div>
          </div>
        </div>
        <button type="submit" id="save" class="btn btn-primary">Save</button>
        <a href="{{ route('content') }}"><button type="button" class="btn btn-default">Cancel</button></a>
      </form>
    </div>
  </div>
@endsection

@section('script')
<script src="{{ asset('js/summernote-bs4.min.js') }}"></script>
<script type="text/javascript">
    $('#content').summernote('codeview.toggle');
</script>
@endsection
