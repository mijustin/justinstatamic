@extends('layout')

@section('content')
    <redirects-list
        :columns='{{ $columns }}'
        :translations="{{ $translations }}"
        :actions="['edit', 'delete']"
        get="{{ route('redirects.manual.get') }}"
        delete="{{ route('redirects.manual.delete') }}"
        reorder="{{ route('redirects.manual.reorder') }}"
        inline-template v-cloak
    >
        <div class="listing redirects-listing">

            <div class="flex flex-wrap justify-between lg_flex-no-wrap items-center mb-3">
                <h1 class="flex-1 mb-8 lg_mb-0">{{ $title }}</h1>
                <div class="controls flex items-center w-full lg:w-auto">
                    <template v-if="!reordering">
                        <button type="button" @click="enableReorder" class="btn ml-1">
                            {{ t('reorder') }}
                        </button>

                        <a href="{{ route('redirects.manual.create') }}" class="btn btn-primary ml-1">{{ $create_title }}</a>
                    </template>
                    <template v-else>
                        <button type="button" @click="cancelOrder" class="btn ml-1">
                            {{ t('cancel') }}
                        </button>
                        <button type="button" @click="saveOrder" class="btn btn-primary ml-1">
                            {{ t('save_order') }}
                        </button>
                    </template>
                </div>
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
