@extends('layout')

@section('content')
<page-tree inline-template v-cloak>
    <div id="pages">
        <div class="flex items-center flex-wrap mb-3">
            <h1 class="w-full text-center mb-2 md:mb-0 md:text-left md:w-auto md:flex-1">{{ translate('cp.nav_pages') }}</h1>
            <div class="controls flex flex-wrap justify-center md:block items-center w-full md:w-auto">
                <div class="btn-group mt-1 md:mt-0">
                    <select-fieldtype :data.sync="showDrafts" :options="draftOptions"></select-fieldtype>
                </div>
                <div class="btn-group ml-1 mt-1 md:mt-0" v-if="locales.length > 1">
                    <select-fieldtype :data.sync="locale" :options="locales"></select-fieldtype>
                </div>
                <div class="btn-group ml-1 mt-1 md:mt-0" v-if="arePages">
                    <button type="button" class="btn btn-default" v-on:click="expandAll" v-if="hasChildren">
                        {{ translate('cp.expand') }}
                    </button>
                    <button type="button" class="btn btn-default" v-on:click="collapseAll" v-if="hasChildren">
                        {{ translate('cp.collapse') }}
                    </button>
                    <button type="button" class="btn btn-default" v-on:click="toggleUrls" v-text="translate('cp.show_'+show)">
                    </button>
                </div>
                @can('pages:create')
                    <button type="button" class="btn btn-primary ml-1 mt-1 md:mt-0" @click="createPage('/')">
                        {{ t('create_page_button') }}
                    </button>
                @endcan
                @can('pages:reorder')
                    <div class="btn-group btn-group-primary ml-1" v-if="arePages && changed">
                        <button type="button" class="btn btn-primary" v-if="! saving" @click="save">
                            {{ translate('cp.save_changes') }}
                        </button>
                        <span class="btn btn-primary btn-has-icon-right disabled" v-if="saving">
                            {{ translate('cp.saving') }} <i class="icon icon-circular-graph animation-spin"></i>
                        </span>
                    </div>
                @endcan
            </div>
        </div>

        <div :class="{'page-tree': true, 'show-urls': showUrls}">
            <div class="loading" v-if="loading">
                <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
            </div>

            <div class="saving" v-if="saving">
                <div class="inner">
                    <i class="icon icon-circular-graph animation-spin"></i> {{ translate('cp.saving') }}
                </div>
            </div>

            <ul class="tree-home list-unstyled" v-if="!loading">
                <branch url="/"
                        :home="true"
                        title="{{ array_get($home, 'title') }}"
                        uuid="{{ array_get($home, 'id')}}"
                        :edit-url="homeEditUrl"
                        :has-entries="{{ bool_str(array_get($home, 'has_entries')) }}"
                        entries-url="{{ array_get($home, 'entries_url') }}"
                        create-entry-url="{{ array_get($home, 'create_entry_url') }}">
                </branch>
            </ul>

            <branches :pages="pages" :depth="1" :sortable="isSortable"></branches>
        </div>

        <create-page :locale="locale"></create-page>
        <mount-collection></mount-collection>
        <audio v-el:click>
            <source src="{{ cp_resource_url('audio/click.mp3') }}" type="audio/mp3">
        </audio>
        <audio v-el:card_drop>
            <source src="{{ cp_resource_url('audio/card_drop.mp3') }}" type="audio/mp3">
        </audio>
        <audio v-el:card_set>
            <source src="{{ cp_resource_url('audio/card_set.mp3') }}" type="audio/mp3">
        </audio>
    </div>
</page-tree>
@endsection
