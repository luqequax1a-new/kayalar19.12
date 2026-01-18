@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('question::questions.question')]))

    <li><a href="{{ route('admin.questions.index') }}">{{ trans('question::questions.questions') }}</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('question::questions.question')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.questions.update', $question) }}" class="form-horizontal" id="question-edit-form" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}

        {!! $tabs->render(compact('question')) !!}
    </form>
@endsection

@push('shortcuts')
    <dl class="dl-horizontal">
        <dt><code>b</code></dt>
        <dd>{{ trans('admin::admin.shortcuts.back_to_index', ['name' => trans('question::questions.question')]) }}</dd>
    </dl>
@endpush

@push('scripts')
    <script type="module">
        keypressAction([
            { key: 'b', route: "{{ route('admin.questions.index') }}" },
        ]);
    </script>
@endpush
