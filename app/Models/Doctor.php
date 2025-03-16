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



/**
 * App\Model\Doctor
 *
 * @property int $id
 * @property string $professional_title
 * @property string $bio
 * @property string $user_fullname
 * @property double $visit_price
 * @property DoctorStatusEnum::string $status
 * @property User $user
 * @property string $created_at
 * @property int $user_id
 * @property int $year_experience
 * @property string $updated_at
 *
 * @property User $user
 * @property ReviewRating|null $reviewRatings
 * @property PatientRecord|null $patient_records
 * @property Appointment[]|null $appointments
 * @property Speciality[]|null $specialities
 * @property Qualification[]|null $qualifications
 * @property mixed $grouped_working_hours
 */
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DoctorStatusEnum::class,
            'visit_price' => 'double',
            'year_experience' => 'integer',
        ];
    }

    /**
     * user
     *
     * @return BelongsTo<User,Doctor>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * specialities
     *
     * @return BelongsToMany<Speciality>
     */
    public function specialities(): BelongsToMany
    {
        return $this->belongsToMany(Speciality::class, 'doctor_speciality');
    }

    /**
     * appointments
     *
     * @return HasMany<Appointment>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * doctor_speciality
     *
     * @return HasMany<DoctorSpeciality>
     */
    public function doctor_speciality(): HasMany
    {
        return $this->hasMany(DoctorSpeciality::class);
    }

    /**
     * qualifications
     *
     * @return HasMany<Qualification>
     */
    public function qualifications(): HasMany
    {
        return $this->hasMany(Qualification::class)
            ->orderBy('procurement_date', 'desc');
    }

    /**
     * documents_for_validations
     *
     * @return HasMany<DocumentsForValidation>
     */
    public function documents_for_validations(): HasMany
    {
        return $this->hasMany(DocumentsForValidation::class);
    }

    /**
     * work_places
     *
     * @return HasMany
     */
    /**
     * work_places
     *
     * @return HasMany<WorkPlace>
     */
    public function work_places(): HasMany
    {
        return $this->hasMany(WorkPlace::class);
    }

    /**
     * working_hours
     *
     * @return HasMany<WorkingHour>
     */
    public function working_hours(): HasMany
    {
        return $this->hasMany(WorkingHour::class);
    }

    /**
     * patient_records
     *
     * @return HasMany<PatientRecord>
     */
    public function patient_records(): HasMany
    {
        return $this->hasMany(PatientRecord::class);
    }

    /**
     * review_ratings
     *
     * @return HasMany<ReviewRating>
     */
    public function review_ratings(): HasMany
    {
        return $this->hasMany(ReviewRating::class)
            ->orderBy('created_at');
    }

    /**
     * getUserFullnameAttribute
     *
     * @return string|null
     */
    public function getUserFullnameAttribute(): string|null
    {
        return $this->user->fullname;
    }

    /**
     * getUserSexAttribute
     *
     * @return mixed
     */
    public function getUserSexAttribute(): mixed
    {
        return $this->user->sex;
    }

    /**
     * patients
     *
     * @return mixed
     */
    public function patients(): mixed
    {
        return $this->hasManyThrough(Patient::class, Appointment::class, 'doctor_id', 'id', 'id', 'patient_id');
    }

    // Compter le nombre de patients uniques pour un médecin donné
    /**
     * patientsCount
     *
     * @return mixed
     */
    public function patientsCount(): mixed
    {
        return $this->hasMany(Appointment::class)
            ->where('status', AppointmentStatusEnum::ACCEPTED->value)
            ->orWhere('status', AppointmentStatusEnum::FINISHED->value)
            ->where('date_appointment', '<=', now())
            ->select('doctor_id', DB::raw('count(distinct patient_id) as aggregate'))
            // ->selectRaw('doctor_id, count(*) as aggregate')
            ->groupBy('doctor_id');
    }
}
