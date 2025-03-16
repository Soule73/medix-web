<?php

namespace App\Models;

use App\Enums\DocumentsForValidation\DocumentsForValidationStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Model\DocumentsForValidation
 *
 * @property int $id
 * @property string $name
 * @property string $message
 * @property string $path
 * @property string $type
 * @property DocumentsForValidationStatusEnum::string $status
 * @property int $doctor_id
 * @property User $user
 * @property string $created_at
 * @property string $updated_at
 *
 */
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DocumentsForValidationStatusEnum::class,
        ];
    }
}
