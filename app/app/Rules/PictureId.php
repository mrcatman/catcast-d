<?php

namespace App\Rules;

use App\Models\Picture;
use Illuminate\Contracts\Validation\Rule;

class PictureId implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $picture = Picture::find($value);
        return !!$picture;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'global.not_found';
    }
}
