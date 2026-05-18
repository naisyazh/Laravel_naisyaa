@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <h4>Hello! let's get started</h4>
    <h6 class="font-weight-light">Sign in to continue.</h6>

    <form class="pt-3" method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <input type="email"
                name="email"
                class="form-control form-control-lg"
                placeholder="Email"
                required>
        </div>

        <div class="form-group">
            <input type="password"
                name="password"
                class="form-control form-control-lg"
                placeholder="Password"
                required>
        </div>

        <div class="mt-3">
            <button type="submit"
                class="btn btn-block btn-gradient-primary btn-lg font-weight-medium auth-form-btn">
                SIGN IN
            </button>
        </div>
    </form>
@endsection