@extends('layouts.app')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Payment Details
                    <img src="{{ asset('images/stripe_logo.png') }}" width="50px" style="float: right;">
                </div>
                <div class="card-body">
                    @if (Session::has('error'))
                    <div class="alert alert-danger text-center">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
                        <p>{{ Session::get('error') }}</p>
                    </div>
                    @endif

                    <form role="form" action="{{ route('handlePayment') }}" method="post" class="validation" data-cc-on-file="false" data-stripe-publishable-key="{{ getSetting('STRIPE_KEY') }}">
                        @csrf

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Name on Card</label>

                            <div class="col-md-12">
                                <input type="text" class="form-control" required autofocus>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Card Number</label>

                            <div class="col-md-12">
                                <input type="number" class="form-control card-number" size="20" autocomplete="off" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-xs-12 col-md-4 form-group cvc required">
                                <label class="col-form-label">CVC</label>
                                <input autocomplete="off" class="form-control card-cvc" placeholder="e.g 415" size="4" type="number" />
                            </div>
                            <div class="col-xs-12 col-md-4 form-group expiration required">
                                <label class="col-form-label">Expiration Month</label>
                                <input class="form-control card-expiry-month" placeholder="MM" min="1" max="12" size="2" type="number" />
                            </div>
                            <div class="col-xs-12 col-md-4 form-group expiration required">
                                <label class="col-form-label">Expiration Year</label>
                                <input class="form-control card-expiry-year" placeholder="YYYY" size="4" type="number" />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 hide error form-group">
                                <div class="alert-danger alert">Fix the errors before you begin.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-5 col-md-5 col-lg-3">
                                <button id="payNow" class="btn btn-danger btn-lg btn-block" type="submit">Pay Now: {{ getCurrencySymbol() . $price }}</button>
                            </div>
                        </div>
                        <input type="hidden" name="type" value="{{ $type }}">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://js.stripe.com/v2/"></script>
@endsection