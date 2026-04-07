<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MahasiswaCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return Auth::check();
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'nrp' => 'required|max:10',
            'nama' => 'required|max:255',
            'prodi' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'nrp.required' => 'NRP wajib diisi',
            'nrp.max' => 'NRP maksimal 10 karakter',
            'nama.required' => 'Nama wajib diisi',
            'nama.max' => 'Nama maksimal 255 karakter',
            'prodi.required' => 'Program Studi wajib diisi',
        ];
    }
}
