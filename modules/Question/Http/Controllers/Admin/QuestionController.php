<?php

namespace Modules\Question\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Modules\Question\Entities\Question;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Admin\Ui\Facades\TabManager;
use Illuminate\Support\Str;

class QuestionController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Question::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'question::questions.question';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'question::admin';

    /**
     * Route prefix of the resource.
     *
     * @var string
     */
    protected $routePrefix = 'admin.questions';

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $question = Question::findOrFail($id);
        
        $tabs = TabManager::get('questions');

        return view('question::admin.edit', compact('question', 'tabs'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function update($id)
    {
        $question = Question::findOrFail($id);

        $data = request()->all();
        if ($data['answer'] && !$question->answer) {
            $data['answered_at'] = now();
        }

        $question->update($data);

        return back()->withSuccess(trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]));
    }

    /**
     * Destroy resources by given ids.
     *
     * @param string $ids
     *
     * @return void
     */
    public function destroy(string $ids)
    {
        Question::whereIn('id', explode(',', $ids))
            ->delete();
    }
}
