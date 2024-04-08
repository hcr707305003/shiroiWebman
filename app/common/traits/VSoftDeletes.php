<?php

namespace app\common\traits;

use app\common\handle\VSoftDeletingScope;
use Illuminate\Database\Eloquent\Concerns\HasGlobalScopes;
use Illuminate\Database\Eloquent\SoftDeletes;

trait VSoftDeletes
{
    use SoftDeletes;

    use HasGlobalScopes;

    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootSoftDeletes()
    {
        static::addGlobalScope(new VSoftDeletingScope);
    }
}
