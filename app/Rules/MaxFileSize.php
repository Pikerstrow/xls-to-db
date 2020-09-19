<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

class MaxFileSize implements Rule
{
    private $acceptable_size;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->acceptable_size = UploadedFile::getMaxFilesize();
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return $value->getSize() <= $this->acceptable_size;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        $acceptable_size_in_kb = $this->acceptable_size / 1024;
        return "Максимально допустимий ромір файлу {$acceptable_size_in_kb} кілобайт";
    }
}
