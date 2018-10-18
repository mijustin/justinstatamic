<div class="cp-head">

    <div class="logo">
        <a href="{{ route('cp') }}" class="flex items-center">
              {!! inline_svg('statamic-logo') !!}
              <span class="version" v-cloak>@{{ version }}</span>
        </a>
    </div>

    <typeahead v-ref:search :src="cp_url('search/perform')" :limit="10" class="flex-1"></typeahead>

    <div class="head-links pl-1 flex items-center">
        <a href="{{ route('site') }}" target="_blank" v-cloak v-tip :tip-text="translate('cp.view_site')">
            <span class="icon icon-popup"></span>
        </a>

        <a class="dropdown-toggle ml-1 hide md:block" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            @if (\Statamic\API\Config::get('users.enable_gravatar'))
                <img src="{{ \Statamic\API\User::getCurrent()->getAvatar() }}" alt="" height="32" width="32" class="round ml-8 z-depth-1">
            @else
                <div class="icon-user-initials round ml-8 z-depth-1">{{ \Statamic\API\User::getCurrent()->userInitials() }}</div>
            @endif
        </a>
        <ul class="dropdown-menu hide md:block">
            <li><a href="{{ route('account') }}">{{ t('profile') }}</a></li>
            <li><a href="{{ route('account.password') }}">{{ t('change_password') }}</a></li>
            <li class="divider"></li>
            <li><a href="{{ route('logout') }}">{{ t('sign_out') }}</a></li>
        </ul>
    </div>

</div>
