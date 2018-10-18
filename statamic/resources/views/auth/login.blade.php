@extends('outside')
@section('box-attributes', 'v-cloak')

@section('content')

    <login inline-template :show-email-login="!{{ bool_str($oauth) }}" :has-error="{{ bool_str(count($errors) > 0) }}">

        @if ($oauth)
            <div class="login-oauth-providers">
                @foreach (Statamic\API\OAuth::providers() as $provider => $data)
                    <div class="provider">
                        <a href="{{ Statamic\API\OAuth::route($provider) }}?redirect={{ parse_url(route('cp'))['path'] }}" class="btn btn-block btn-primary">
                            {{ t('login_with', ['provider' => array_get($data, 'label', \Statamic\API\Str::title($provider))]) }}
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="login-or">or</div>

            <div class="login-with-email" v-if="! showEmailLogin">
                <a class="btn btn-block" @click.prevent="showEmailLogin = true">
                    {{ t('login_with', ['provider' => t(\Statamic\API\Config::get('users.login_type'))]) }}
                </a>
            </div>
        @endif

        <form method="POST" v-show="showEmailLogin" class="email-login">
            {!! csrf_field() !!}

            <input type="hidden" name="referer" value="{{ $referer }}" />

            <div class="mb-4">
                <label>
                @if (\Statamic\API\Config::get('users.login_type') === 'email')
                    {{ trans_choice('cp.emails', 1) }}
                @else
                    {{ trans('cp.username') }}
                @endif
                </label>
                <input type="text" class="form-control" name="username" value="{{ old('username') }}" autofocus>
            </div>

            <div class="mb-4">
                <label>{{ t('password') }}</label>
                <input type="password" class="form-control" name="password" id="password">
            </div>

            <div class="mb-4">
                <input type="checkbox" class="form-control" name="remember" id="checkbox-0">
                <label for="checkbox-0" class="normal">{{ trans('cp.remember_me') }}</label>
            </div>

            <div>
                <button type="submit" class="btn btn-primary btn-block">{{ trans('cp.login') }}</button>
                @if (! $oauth)
                    <small class="block mt-16"><a href="{{ route('login.reset')}}">{{ t('forgot_password')}}</a></small>
                @endif
            </div>
        </form>
    </login>

@endsection
