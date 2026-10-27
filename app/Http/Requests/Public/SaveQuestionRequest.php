<?php

namespace App\Http\Requests\Public;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Новый вопрос и правка: заголовок с симптомом, содержательное описание или фото, необязательные модель и код ошибки. */
class SaveQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $edited = $this->edited();

        // при правке чужой вопрос закрыт ещё до проверки полей
        return $this->user() !== null && (! $edited || $this->user()->can('update', $edited));
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:12', 'max:150', Rule::unique('questions', 'title')->whereNull('deleted_at')->ignore($this->edited()?->id)],
            'appliance_id' => ['required', 'exists:appliances,id'],
            'model' => ['nullable', 'string', 'max:120'],
            'error_code' => ['nullable', 'string', 'max:60'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000', function (string $attribute, mixed $value, \Closure $fail) {
                if (mb_strlen(trim((string) $value)) < 40 && ! $this->filled('images')) {
                    $fail('Опишите проблему подробнее: когда началось и что уже проверяли (хотя бы 40 знаков) или приложите фото.');
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Напишите, что случилось.',
            'title.min' => 'Заголовок слишком короткий: опишите симптом и прибор (не меньше 12 знаков).',
            'title.max' => 'Заголовок длиннее 150 знаков: сократите, подробности перенесите в описание.',
            'title.unique' => 'Вопрос с таким заголовком уже есть. Уточните его (модель, код ошибки) или найдите существующий вопрос.',
            'appliance_id.required' => 'Выберите тип прибора.',
        ];
    }

    /** Правимый вопрос (по адресу), либо null при создании. */
    public function edited(): ?Question
    {
        $slug = $this->route('question');

        return is_string($slug) && $this->isMethod('PUT') ? Question::where('slug', $slug)->first() : null;
    }
}
