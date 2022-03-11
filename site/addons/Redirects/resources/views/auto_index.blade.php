@extends('layout')

@section('content')
    <redirects-list
        :columns='{{ $columns }}'
        :translations="{{ $translations }}"
        :actions="['delete']"
        get="{{ route('redirects.auto.get') }}"
        delete="{{ route('redirects.auto.delete') }}"
        inline-template v-cloak
    >
        <div class="listing redirects-listing">

            <div class="flex flex-wrap justify-between lg_flex-no-wrap items-center mb-3">
                <h1 class="flex-1 mb-8 lg_mb-0">{{ $title }}</h1>
            </div>

            <div class="card flush dossier-for-mobile">
                <div class="loading" v-if="loading">
                    <span class="icon icon-circular-graph animation-spin"></span> {{ t('loading') }}
                </div>
                <dossier-table :options="tableOptions" :items="items"></dossier-table>
            </div>
        </div>
    </redirects-list>
@endsection
