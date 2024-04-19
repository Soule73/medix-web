<?php

namespace App\Models;

use App\Enums\Appointment\AppointmentStatusEnum;
use App\Enums\Doctor\DoctorStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Doctor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'professional_title',
        'status',
        'bio',
        'visit_price',
        'user_id',
        'year_experience',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DoctorStatusEnum::class,
            'visit_price' => 'double',
            'year_experience' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialities(): BelongsToMany
    {
        return $this->belongsToMany(Speciality::class, 'doctor_speciality');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function doctor_speciality(): HasMany
    {
        return $this->hasMany(DoctorSpeciality::class);
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(Qualification::class);
    }

    public function documents_for_validations(): HasMany
    {
        return $this->hasMany(DocumentsForValidation::class);
    }

    public function work_places(): HasMany
    {
        return $this->hasMany(WorkPlace::class);
    }

    public function working_hours(): HasMany
    {
        return $this->hasMany(WorkingHour::class);
    }

    public function patient_records(): HasMany
    {
        return $this->hasMany(PatientRecord::class);
    }

    public function review_ratings(): HasMany
    {
        return $this->hasMany(ReviewRating::class);
    }

    public function getUserFullnameAttribute()
    {
        return $this->user->fullname;
    }

    public function patients()
    {
        return $this->hasManyThrough(Patient::class, Appointment::class, 'doctor_id', 'id', 'id', 'patient_id');
    }

    public function patientsCount()
    {
        return $this->hasMany(Appointment::class)
            ->where('status', AppointmentStatusEnum::ACCEPTED->value)
            ->orWhere('status', AppointmentStatusEnum::ACCEPTED->value)
            ->where('date_appointment', '<=', now())
            ->select('doctor_id', DB::raw('count(distinct patient_id) as aggregate'))
            // ->selectRaw('doctor_id, count(*) as aggregate')
            ->groupBy('doctor_id');
    }
    // Compter le nombre de patients uniques pour un médecin donné

}
