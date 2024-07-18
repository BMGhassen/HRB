@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="login form">
      <header>S'authentifier</header>
      <form method="POST" action="{{ route('login') }}">
        @csrf
        <input type="text" id="email" placeholder="Saisir votre email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
        @error('email')
            <div class="alert alert-danger" role="alert" style="color:crimson">{{ $message }}</div>
        @enderror
        <input type="password" id="password" placeholder="Saisir votre mot de passe" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
        @error('password')
                <div class="alert alert-danger" role="alert" style="color:crimson">{{ $message }}</div>
        @enderror
        @if (Route::has('password.request'))
            <a class="btn btn-link"  href="{{ route('password.request') }}">
                {{ __('Mot de passe oublié ?') }}
            </a>
        @endif
        <button type="submit">
            {{ __('Valider') }}
        </button>
      </form>
      <div class="signup">
        <span class="signup">Vous n'avez pas de compte ?
         <a href="{{route('register')}}">S'inscrire</a>
        </span>
      </div>
    </div>
  </div>
@endsection
