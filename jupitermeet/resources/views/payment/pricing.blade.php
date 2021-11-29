@extends('layouts.app')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
    <div class="container-fluid text-center">
        <h1 class="mb-3">Choose your plan</h1>
        <div class="container plan-selection mb-5 mb-md-0">
            <div class="mb-3 ">
                <input type="radio" name="period" value="monthly" id="monthly" checked>
                <label for="monthly" class="btn">Monthly</label>
                <input type="radio" name="period" value="yearly" id="yearly">
                <label for="yearly" class="btn">Yearly</label>
            </div>
            <div class="card-deck mb-3 text-center">
                <div class="card">
                    <div class="card-header">
                        {{ getSetting('PRICING_PLAN_NAME_FREE') }}
                    </div>
                    <div class="card-body">
                        @if (auth()->user() && auth()->user()->plan_type == 'free')
                            <div class="ribbon-wrapper ribbon-lg">
                                <div class="ribbon bg-primary">
                                    Current
                                </div>
                            </div>
                        @endif
                        <h3>Free</h3>
                        <ul class="list-unstyled mt-3 mb-4">
                            <li>{{ getSetting('MEETING_LIMIT') }} Meetings</li>
                            <li>Limited Meeting Time</li>
                        </ul>
                        <a href="{{ route('register') }}">
                            <button type="button" class="btn btn-secondary" @auth disabled @endauth>Join Now</button>
                        </a>
                    </div>
                </div>
            
                <div class="card">
                    <div class="card-header">
                        {{ getSetting('PRICING_PLAN_NAME_PAID') }}
                    </div>
                    <div class="card-body">
                        @if (auth()->user() && auth()->user()->plan_type == 'paid')
                            <div class="ribbon-wrapper ribbon-lg">
                                <div class="ribbon bg-primary">
                                    Current
                                </div>
                            </div>
                        @endif
                        <h3 id="montlyPrice">{{ getCurrencySymbol() . getSetting('MONTHLY_PRICE') }} <small>/month</small></h3>
                        <h3 id="yearlyPrice" hidden>{{  getCurrencySymbol() . getSetting('YEARLY_PRICE') }} <small>/year</small></h3>
                        <ul class="list-unstyled mt-3 mb-4">
                            <li>Unlimited Meetings</li>
                            <li>Unlimited Meeting Time</li>
                        </ul>
                        <form action="payment">
                            <input type="hidden" id="type" name="type" value="monthly">
                            <button type="submit" class="btn btn-primary" @if (auth()->user() && auth()->user()->plan_type == 'paid') disabled @endif>Buy Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
