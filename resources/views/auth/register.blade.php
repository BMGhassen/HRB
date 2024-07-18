@extends('layouts.app')

@section('content')

<div class="container">
    <div class="registration form">
        <header>Inscription</header>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <input id="name" type="text" placeholder="Nom et Prénom" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

            @error('name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
          <input placeholder="Saisir votre email" id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
          @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
          <input type="password" placeholder="Créer un mot de passe" id="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
          @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
          <input type="password" placeholder="Confirmer votre mot de passe" id="password-confirm" class="form-control" name="password_confirmation" required autocomplete="new-password">
          <button type="submit" class="btn btn-primary">
            {{ __('Valider') }}
            </button>
        </form>
        <div class="signup">
          <span class="signup">Vous avez déjà un compte?
           <a href="{{route('login')}}">Se connecter</a>
          </span>
        </div>
     </div>
</div>
@endsection
