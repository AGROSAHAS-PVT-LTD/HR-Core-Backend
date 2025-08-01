<?php

namespace App\Http\Requests\Api\Holiday;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GetAllRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'skip' => 'nullable:numeric',
      'take' => 'nullable:numeric',
      'year' => 'nullable:numeric'
    ];
  }
}
