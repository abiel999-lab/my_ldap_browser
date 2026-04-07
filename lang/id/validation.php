<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute harus diterima.',
    'accepted_if' => ':attribute harus diterima ketika :other adalah :value.',
    'active_url' => ':attribute bukan URL yang valid.',
    'after' => ':attribute harus berupa tanggal setelah :date.',
    'after_or_equal' => ':attribute harus berupa tanggal setelah atau sama dengan :date.',
    'alpha' => ':attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, strip, dan garis bawah.',
    'alpha_num' => ':attribute hanya boleh berisi huruf dan angka.',
    'array' => ':attribute harus berupa array.',
    'before' => ':attribute harus berupa tanggal sebelum :date.',
    'before_or_equal' => ':attribute harus berupa tanggal sebelum atau sama dengan :date.',
    'between' => [
        'numeric' => ':attribute harus antara :min dan :max.',
        'file' => ':attribute harus antara :min dan :max kilobyte.',
        'string' => ':attribute harus antara :min dan :max karakter.',
        'array' => ':attribute harus memiliki antara :min dan :max item.',
    ],
    'boolean' => 'Field :attribute harus bernilai true atau false.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Kata sandi salah.',
    'date' => ':attribute bukan tanggal yang valid.',
    'date_equals' => ':attribute harus berupa tanggal yang sama dengan :date.',
    'date_format' => ':attribute tidak cocok dengan format :format.',
    'declined' => ':attribute harus ditolak.',
    'declined_if' => ':attribute harus ditolak ketika :other adalah :value.',
    'different' => ':attribute dan :other harus berbeda.',
    'digits' => ':attribute harus terdiri dari :digits digit.',
    'digits_between' => ':attribute harus antara :min dan :max digit.',
    'dimensions' => ':attribute memiliki dimensi gambar yang tidak valid.',
    'distinct' => 'Field :attribute memiliki nilai duplikat.',
    'doesnt_end_with' => ':attribute tidak boleh diakhiri dengan salah satu dari: :values.',
    'doesnt_start_with' => ':attribute tidak boleh diawali dengan salah satu dari: :values.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'ends_with' => ':attribute harus diakhiri dengan salah satu dari: :values.',
    'enum' => ':attribute yang dipilih tidak valid.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'file' => ':attribute harus berupa file.',
    'filled' => 'Field :attribute harus memiliki nilai.',
    'gt' => [
        'numeric' => ':attribute harus lebih besar dari :value.',
        'file' => ':attribute harus lebih besar dari :value kilobyte.',
        'string' => ':attribute harus lebih dari :value karakter.',
        'array' => ':attribute harus memiliki lebih dari :value item.',
    ],
    'gte' => [
        'numeric' => ':attribute harus lebih besar atau sama dengan :value.',
        'file' => ':attribute harus lebih besar atau sama dengan :value kilobyte.',
        'string' => ':attribute harus lebih besar atau sama dengan :value karakter.',
        'array' => ':attribute harus memiliki :value item atau lebih.',
    ],
    'image' => ':attribute harus berupa gambar.',
    'in' => ':attribute yang dipilih tidak valid.',
    'in_array' => 'Field :attribute tidak terdapat dalam :other.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'ip' => ':attribute harus berupa alamat IP yang valid.',
    'ipv4' => ':attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => ':attribute harus berupa alamat IPv6 yang valid.',
    'json' => ':attribute harus berupa string JSON yang valid.',
    'lowercase' => ':attribute harus dalam huruf kecil.',
    'lt' => [
        'numeric' => ':attribute harus kurang dari :value.',
        'file' => ':attribute harus kurang dari :value kilobyte.',
        'string' => ':attribute harus kurang dari :value karakter.',
        'array' => ':attribute harus memiliki kurang dari :value item.',
    ],
    'lte' => [
        'numeric' => ':attribute harus kurang dari atau sama dengan :value.',
        'file' => ':attribute harus kurang dari atau sama dengan :value kilobyte.',
        'string' => ':attribute harus kurang dari atau sama dengan :value karakter.',
        'array' => ':attribute tidak boleh memiliki lebih dari :value item.',
    ],
    'mac_address' => ':attribute harus berupa alamat MAC yang valid.',
    'max' => [
        'numeric' => ':attribute tidak boleh lebih besar dari :max.',
        'file' => ':attribute tidak boleh lebih besar dari :max kilobyte.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
        'array' => ':attribute tidak boleh memiliki lebih dari :max item.',
    ],
    'max_digits' => ':attribute tidak boleh memiliki lebih dari :max digit.',
    'mimes' => ':attribute harus berupa file dengan tipe: :values.',
    'mimetypes' => ':attribute harus berupa file dengan tipe: :values.',
    'min' => [
        'numeric' => ':attribute harus minimal :min.',
        'file' => ':attribute harus minimal :min kilobyte.',
        'string' => ':attribute harus minimal :min karakter.',
        'array' => ':attribute harus memiliki setidaknya :min item.',
    ],
    'min_digits' => ':attribute harus memiliki setidaknya :min digit.',
    'multiple_of' => ':attribute harus kelipatan dari :value.',
    'not_in' => ':attribute yang dipilih tidak valid.',
    'not_regex' => 'Format :attribute tidak valid.',
    'numeric' => ':attribute harus berupa angka.',
    'password' => [
        'letters' => ':attribute harus mengandung setidaknya satu huruf.',
        'mixed' => ':attribute harus mengandung setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers' => ':attribute harus mengandung setidaknya satu angka.',
        'symbols' => ':attribute harus mengandung setidaknya satu simbol.',
        'uncompromised' => ':attribute yang diberikan telah bocor dalam data kebocoran. Silakan gunakan yang berbeda.',
    ],
    'present' => 'Field :attribute harus ada.',
    'prohibited' => 'Field :attribute dilarang.',
    'prohibited_if' => 'Field :attribute dilarang ketika :other adalah :value.',
    'prohibited_unless' => 'Field :attribute dilarang kecuali :other ada dalam :values.',
    'prohibits' => 'Field :attribute melarang :other untuk ada.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => 'Field :attribute wajib diisi.',
    'required_array_keys' => 'Field :attribute harus berisi entri untuk: :values.',
    'required_if' => 'Field :attribute wajib diisi ketika :other adalah :value.',
    'required_if_accepted' => 'Field :attribute wajib diisi ketika :other diterima.',
    'required_unless' => 'Field :attribute wajib diisi kecuali :other ada dalam :values.',
    'required_with' => 'Field :attribute wajib diisi ketika :values ada.',
    'required_with_all' => 'Field :attribute wajib diisi ketika :values ada.',
    'required_without' => 'Field :attribute wajib diisi ketika :values tidak ada.',
    'required_without_all' => 'Field :attribute wajib diisi ketika tidak ada satu pun dari :values yang ada.',
    'same' => ':attribute dan :other harus cocok.',
    'size' => [
        'numeric' => ':attribute harus berukuran :size.',
        'file' => ':attribute harus berukuran :size kilobyte.',
        'string' => ':attribute harus berjumlah :size karakter.',
        'array' => ':attribute harus mengandung :size item.',
    ],
    'starts_with' => ':attribute harus diawali dengan salah satu dari: :values.',
    'string' => ':attribute harus berupa string.',
    'timezone' => ':attribute harus zona waktu yang valid.',
    'unique' => ':attribute sudah digunakan.',
    'uploaded' => ':attribute gagal diunggah.',
    'uppercase' => ':attribute harus huruf besar.',
    'url' => ':attribute harus URL yang valid.',
    'uuid' => ':attribute harus UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
