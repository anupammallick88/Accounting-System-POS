<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class DocumentAndNote extends Model
{
    use LogsActivity;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    protected static $logUnguarded = true;
    protected static $logOnlyDirty = true;


    /**
    * Get all of the owning notable models.
    */
    public function notable()
    {
        return $this->morphTo();
    }
    
    public function media()
    {
        return $this->morphMany(\App\Media::class, 'model');
    }

    /**
     * Get the user who added note.
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }
}
