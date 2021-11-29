@extends('layouts.app')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
  <div class="container">
      <h3 class="mb-3">Hi, {{ auth()->user()->username }}!</h3>

      @if (Session::has('success'))
          <div class="alert alert-success text-center" role="alert">
              {{ Session::get('success') }}
          </div>
      @endif

      @if(auth()->user()->plan_status == 'active')
          <p>Your plan is <span class="badge badge-success">Active</span> and is valid till {{ !$userPlan->isEmpty() ? $userPlan[0]->plan_end_date : '-' }}.</p>
      @elseif(auth()->user()->plan_status == 'inactive')
          <p>Your plan is <span class="badge badge-info">Inactive</span>, <a href="{{ route('pricing') }}">Upgrade now</a>!</p>
      @else
          <p>Your plan is <span class="badge badge-danger">Expired</span>, <a href="{{ route('pricing') }}">Upgrade now</a>!</p>
      @endif

      @if(!$userPlan->isEmpty())
      <div class="table-responsive mb-3">
          <table class="table table-striped">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Amount</th>
                <th scope="col">Type</th>
                <th scope="col">Currency</th>
                <th scope="col">Gateway</th>
                <th scope="col">Plan Start Date</th>
                <th scope="col">Plan End Date</th>
              </tr>
            </thead>
            <tbody>
               @foreach ($userPlan as $key => $value)
              <tr>
                <th scope="row">{{ $key + 1 }}</th>
                <td>{{ $value->amount }}</td>
                <td>{{ ucfirst($value->type) }}</td>
                <td>{{ $value->currency }}</td>
                <td>{{ ucfirst($value->gateway) }}</td>
                <td>{{ $value->plan_start_date }}</td>
                <td>{{ $value->plan_end_date }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
      </div>
      @else
          <p>Your transactions will be displayed here.</p>
      @endif
  </div>
@endsection
