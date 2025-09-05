<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail; 
 /**
 * @property string $role
 * @property float $average_rating
 */

class User extends Authenticatable implements MustVerifyEmail, JWTSubject 
{
    use Notifiable;

    public function getJWTIdentifier()
    {
        return $this->getKey(); 
    }

    public function getJWTCustomClaims()
    {
        return []; 
    }

    protected $fillable = [
        'full_name',
        'username',
        'email',
        'password',
        'role',
        'student_number',
        'faculty_number',
        'major',
        'department',
        'average_rating',
        'verification_token',
        'token_expires_at',
        'is_approved',
        'created_by',
        'updated_by',
        'is_board_member',
        'teaching_experience',
        'comments_count',
    ];

    protected $hidden = ['password', 'remember_token', 'verification_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_approved' => 'boolean',
        'token_expires_at' => 'datetime',
        'average_rating' => 'float',
    ];

    

    public function courses()
{
    return $this->belongsToMany(Course::class, 'course_professor', 'professor_id', 'course_id');
}

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'professor_id')
                    ->where('role', 'professor'); 
    }


    public static function generateVerificationToken()
    {
        return Str::random(60);
    }

    public function calculateAverageRating()
    {
        if ($this-> role === 'professor') {
            $this->average_rating = $this->feedbacks()->avg('rating');
            $this->save();
        }
        return $this->average_rating;
    }


    public function scopeApprovedStudents($query)
    {
        return $query->where('role', 'student')->where('is_approved', true);
    }


    public function scopeSortedProfessorsByRating($query)
    {
        return $query->where('role', 'professor')->orderBy('average_rating', 'desc');
    }

    public function taughtCourses()
    {
        return $this->belongsToMany(
            Course::class,
            'course_professor', 
            'professor_id',     
            'course_id'         
        )->select('courses.id', 'courses.title', 'courses.slug', 'courses.course_code', 'courses.avatar' ,'courses.comments_count' ,'course_professor.average_rating' ,'courses.department');
    }
}