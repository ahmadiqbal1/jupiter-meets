@extends('layouts.admin')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
<div class="row">
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <h3>{{ $data['meeting'] }}</h3>

        <p>Meetings</p>
      </div>
      <div class="icon">
        <i class="ion ion-ios-videocam"></i>
      </div>
      <a href="{{ route('meetings') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3>{{ $data['user'] }}</h3>

        <p>Users</p>
      </div>
      <div class="icon">
        <i class="ion ion-person-stalker"></i>
      </div>
      <a href="{{ route('users') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3>{{ getCurrencySymbol() . $data['income'] }}</h3>

        <p>Income</p>
      </div>
      <div class="icon">
        <i class="ion ion-social-usd"></i>
      </div>
      <a href="{{ route('income') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3>{{ ucfirst(getSetting('AUTH_MODE')) }}</h3>

        <p>Auth Mode</p>
      </div>
      <div class="icon">
        <i class="ion ion-locked"></i>
      </div>
      <a href="{{ route('global-config') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-6">
    <div class="card card-danger">
      <div class="card-header">
        <h3 class="card-title">Users</h3>

        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
          <button type="button" class="btn btn-tool" data-card-widget="remove">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <canvas id="usersChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card card-danger">
      <div class="card-header">
        <h3 class="card-title">Income ({{ getCurrencySymbol() }})</h3>

        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
          <button type="button" class="btn btn-tool" data-card-widget="remove">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="chart">
          <canvas id="incomeChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-6">
    <div class="card card-danger">
      <div class="card-header">
        <h3 class="card-title">User Registration</h3>

        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
          <button type="button" class="btn btn-tool" data-card-widget="remove">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="chart">
          <canvas id="userGraph" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card card-danger">
      <div class="card-header">
        <h3 class="card-title">Meetings</h3>

        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
          <button type="button" class="btn btn-tool" data-card-widget="remove">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="chart">
          <canvas id="meetingGraph" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
let freeUsers = "{{ $data['freeUsers'] }}";
let paidUsers = "{{ $data['paidUsers'] }}";
let montlyIncome = JSON.parse("{{ $data['montlyIncome'] }}".replace(/&quot;/g, '"'));
let userGraph = JSON.parse("{{ $data['userGraph'] }}".replace(/&quot;/g, '"'));
let meetingGraph = JSON.parse("{{ $data['meetingGraph'] }}".replace(/&quot;/g, '"'));
let currentYear = "{{ date('Y') }}";
</script>

<script src="{{ asset('js/chart.min.js') }}"></script>
<script src="{{ asset('js/chartjs-plugin-labels.min.js') }}"></script>
<script src="{{ asset('js/admin.js') }}"></script>
@endsection
