<?php

namespace App\Models;

use App\Enums\DocumentsForValidation\DocumentsForValidationStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentsForValidation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'message',
        'type',
        'status',
        'path',

        'doctor_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DocumentsForValidationStatusEnum::class,
        ];
    }
}
