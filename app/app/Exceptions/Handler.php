<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function transformArrayErrors($errors) {
        $new_errors = [];
        foreach ($errors as $key => $val) {
            Arr::set($new_errors, $key, $val);
        }
        return $new_errors;
    }

    public function render($request, Throwable $e) {
        if(get_class($e) == "Illuminate\Database\Eloquent\ModelNotFoundException") {
            return response()->json(['message' => 'errors.item_not_found'],404);
        }

        if ($e instanceof ValidationException) {
            $message = $e->validator->getMessageBag()->first();
            if ($message === 'errors.field_required') {
                $message = 'errors.fill_all_fields';
            }
            $errors = $e->validator->getMessageBag()->getMessages();
            $errors = $this->transformArrayErrors($errors);
            return response()->json([
                'message' => $message,
                'errors' => $errors
            ], 422);
        }
        if ($e instanceof AuthorizationException)  {
            return response()->json(['message' => 'errors.unauthorized'],403);
        }
        return parent::render($request, $e);
    }

}
