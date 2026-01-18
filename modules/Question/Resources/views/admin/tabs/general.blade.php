<div class="row">
    <div class="col-md-8">
        {{ Form::text('customer_name', trans('question::questions.form.customer_name'), $errors, $question, ['readonly' => true]) }}
        {{ Form::text('customer_email', trans('question::questions.form.customer_email'), $errors, $question, ['readonly' => true]) }}
        {{ Form::textarea('question', trans('question::questions.question'), $errors, $question, ['readonly' => true]) }}
        {{ Form::textarea('answer', trans('question::questions.form.answer'), $errors, $question, ['rows' => 5]) }}
        {{ Form::checkbox('is_approved', trans('question::questions.table.status'), trans('question::questions.form.approve_this_question'), $errors, $question) }}
    </div>
</div>
