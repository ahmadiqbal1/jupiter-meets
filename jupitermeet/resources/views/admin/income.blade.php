@extends('layouts.admin')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
<div class="card">
    <div class="card-body">
      @if (getSetting('PAYMENT_MODE') == 'disabled')
        <span class="badge badge-warning p-2 mb-3">The payment mode is disabled, <a href="{{ route('global-config') }}">enable</a> now to accept payments.</span>
      @endif
      <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Amount</th>
          <th>Currency</th>
          <th>Type</th>
          <th>Gateway</th>
          <th>Transaction ID</th>
          <th>Plan Start Date</th>
          <th>Plan End Date</th>
        </tr>
        </thead>
        <tbody>
          @foreach ($plans as $key => $value)
          <tr>
            <td>{{ $value->id }}</td>
            <td>{{ $value->username }}</td>
            <td>{{ $value->amount }}</td>
            <td>{{ $value->currency }}</td>
            <td>
              @if($value->type == "monthly")
                <span class="badge badge-info">Monthly</span>
              @else
                <span class="badge badge-success">Yearly</span>
              @endif
            </td>
            <td>{{ ucfirst($value->gateway) }}</td>
            <td>{{ $value->transaction_id }}</td>
            <td>{{ $value->plan_start_date }}</td>
            <td>{{ $value->plan_end_date }}</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Amount</th>
          <th>Currency</th>
          <th>Type</th>
          <th>Gateway</th>
          <th>Transaction ID</th>
          <th>Plan Start Date</th>
          <th>Plan End Date</th>
        </tr>
        </tfoot>
      </table>
    </div>
  </div>
@endsection
