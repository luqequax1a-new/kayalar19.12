<?php

namespace Modules\Question\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Modules\Question\Entities\Question;
use Modules\Product\Entities\Product;
use Modules\Question\Http\Requests\StoreQuestionRequest;

class ProductQuestionController
{
    /**
     * Display a listing of the resource.
     *
     * @param int $productId
     *
     * @return Response
     */
    public function index($productId)
    {
        $productId = (int) $productId;
        
        $query = Question::query()
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->latest();

        return $query->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param int $productId
     * @param StoreQuestionRequest $request
     *
     * @return Response
     */
    public function store($productId, StoreQuestionRequest $request)
    {
        $product = Product::findOrFail($productId);

        $question = $product->questions()->create([
            'user_id' => auth()->id(),
            'customer_name' => auth()->check() ? auth()->user()->full_name : $request->customer_name,
            'customer_email' => auth()->check() ? auth()->user()->email : $request->customer_email,
            'question' => $request->question,
            'is_approved' => false,
        ]);

        // Notify admin
        try {
            \FleetCart\Services\NotificationService::newQuestion($question);
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => trans('question::questions.storefront.question_submitted'),
            'question' => $question,
        ]);
    }
}
