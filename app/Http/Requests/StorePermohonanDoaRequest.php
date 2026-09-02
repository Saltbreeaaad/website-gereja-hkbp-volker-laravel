<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\MemakaiHoneypot;
use Illuminate\Foundation\Http\FormRequest;

class StorePermohonanDoaRequest extends FormRequest
{
    use MemakaiHoneypot;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->aturanHoneypot() + [
            'nama' => ['nullable', 'string', 'max:255'],
            'kontak' => ['nullable', 'string', 'max:50'],
            'isi' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return ['isi' => 'pokok doa'];
    }
}
