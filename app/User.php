<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;
use Throwable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'address',
        'date_of_birth',
        'created_at',
        'timezone',
        'interest',
        'account',
        'description',
    ];

    protected $dates = [
        'date_of_birth',
        'created_at',
        'updated_at',
    ];

    /**
     * Attempt to interpret date format.
     *
     * @param mixed $value
     */
    public function setDateOfBirthAttribute($value)
    {
        try {
            $date = new Carbon($value);
            $this->attributes['date_of_birth'] = $date;
            $this->attributes['timezone'] = $date->format('e');
        } catch (Throwable $e) {
            // Unable to handle this format.
        }
    }

    /**
     * Add timezone if available.
     *
     * @param mixed $value
     *
     * @return Carbon
     */
    public function getDateOfBirthAttribute($value)
    {
        $date = new Carbon($value);
        return $this->timezone ? $date->tz($this->timezone) : $date;
    }
}
