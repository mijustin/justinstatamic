@extends('layout')

@section('content-class', 'publishing')

@section('content')
    <script>
        Statamic.Publish = {
            contentData: {!! json_encode($data) !!},
            fieldset: {!! json_encode($fieldset) !!},
        };
    </script>

    <publish
        title="{{ $title }}"
        id="{{ $id }}"
        submit-url="{{ $submitUrl }}"
        content-type="addon_redirects"
        :meta-fields="false"
        :is-new="{{ bool_str($id === null) }}"
        :allowSaveAndAddAnother="false"
    ></publish>
@endsection
