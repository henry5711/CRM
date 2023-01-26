<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tak extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'creator_id',
        'fec_ini',
        'fec_end',
        'status_id',
        ];

        public function getUser()
        {
            return $this->hasOne(User::class, 'id', 'user_id');
        }

        public function getCreator()
        {
            return $this->hasOne(User::class, 'id', 'creator_id');
        }

        public function getStatus()
        {
            return $this->hasOne(Status::class,'id','status_id');
        }

        public function getComments(){
            return $this->belongsTo(TakComment::class,'id','tak_id');
        }

        public function labesTaks(){
            return $this->belongsToMany(LabelTaks::class,'label_taks_tak','tak_id','label_taks_id');
        }

        public function scopeFiltro($query, $request)
        {
            return $query
                ->when($request->status_id, function ($query, $status_id) {
                    return $query->where('status_id',$status_id);
                })
                ->when($request->user_id, function ($query, $user_id) {
                    return $query->where('user_id',$user_id);
                })

                ->when($request->creator_id, function ($query, $creator_id) {
                    return $query->where('creator_id',$creator_id);
                })

                ->when($request->fec_ini, function ($query, $fec_ini) use ($request) {
                    $fec_ini=Carbon::parse($fec_ini)->startOfDay()->format('Y-m-d H:i:s');
                    $fec_fin=Carbon::parse($request->fec_end)->startOfDay()->format('Y-m-d H:i:s');
                    return $query->where([
                        ['fec_ini','>=',$fec_ini],
                        ['fec_ini','<=',$fec_fin]
                        ])->orwhere([
                            ['fec_end','<=',$fec_fin],
                            ['fec_end','>=',$fec_ini]
                            ]);
                });
        }

        public function images()
        {
            return $this->morphMany(Image::class , 'imageable');
        }


}
