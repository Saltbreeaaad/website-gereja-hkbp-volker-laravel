<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\MemakaiHoneypot;
use App\Models\PenggunaanGereja;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePenggunaanGerejaRequest extends FormRequest
{
    use MemakaiHoneypot;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Honeypot: tersembunyi dari manusia, lazim diisi bot yang mencoba
        // mengisi semua input formulir secara otomatis.
        return $this->aturanHoneypot() + [
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'nama_pemohon' => ['required', 'string', 'max:255'],
            'kontak' => ['required', 'string', 'max:50', 'regex:/^[0-9+()\-\s]{8,}$/'],
            'tanggal' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:'.now()->addYear()->toDateString()],
            'waktu_mulai' => ['required', 'date_format:H:i'],
            'waktu_selesai' => ['required', 'date_format:H:i', 'after:waktu_mulai'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_kegiatan' => 'nama kegiatan',
            'nama_pemohon' => 'nama pemohon',
            'waktu_mulai' => 'jam mulai',
            'waktu_selesai' => 'jam selesai',
        ];
    }

    public function messages(): array
    {
        return [
            'kontak.regex' => 'Kontak hanya boleh berisi angka, spasi, dan tanda + ( ) -, minimal 8 karakter.',
            'waktu_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'tanggal.after_or_equal' => 'Tanggal tidak boleh sebelum hari ini.',
            'tanggal.before_or_equal' => 'Permohonan hanya dapat diajukan maksimal satu tahun ke depan.',
        ];
    }

    /**
     * Bentrok jadwal diperiksa setelah aturan dasar lolos, supaya pemohon tidak
     * dibanjiri dua jenis pesan kesalahan sekaligus.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $bentrok = PenggunaanGereja::hasApprovedConflict(
                    $this->date('tanggal')->toDateString(),
                    $this->string('waktu_mulai')->toString(),
                    $this->string('waktu_selesai')->toString(),
                );

                if ($bentrok) {
                    $validator->errors()->add(
                        'waktu_mulai',
                        'Jadwal ini bentrok dengan kegiatan lain yang sudah dikonfirmasi pada tanggal & jam tersebut. Silakan pilih jam lain.'
                    );
                }
            },
        ];
    }
}
