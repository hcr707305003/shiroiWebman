<?php
/**
 * @noinspection PhpPossiblePolymorphicInvocationInspection
 * @noinspection PhpUndefinedMethodInspection
 */

namespace app\common\handle;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use ReflectionClass;

/**
 * 处理laravel orm 软删的问题
 */
class VSoftDeletingScope extends SoftDeletingScope
{

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder
     * @param Model $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $reflectionClass = new ReflectionClass($model);
        if ($reflectionClass->hasConstant('DEFAULT_SOFT_DELETE')) {
            $builder->whereRaw($model->getQualifiedDeletedAtColumn() . " = " . $model::DEFAULT_SOFT_DELETE);
        } else {
            $builder->whereNull($model->getQualifiedDeletedAtColumn());
        }
    }
    /**
     * Add the without-trashed extension to the builder.
     *
     * @param Builder $builder
     * @return void
     */
    protected function addWithoutTrashed(Builder $builder)
    {
        $builder->macro('withoutTrashed', function (Builder $builder) {
            $model = $builder->getModel();
            $reflectionClass = new ReflectionClass($model);
            if ($reflectionClass->hasConstant('DEFAULT_SOFT_DELETE')) {
                $builder->withoutGlobalScope($this)->whereRaw(
                    $model->getQualifiedDeletedAtColumn() . " = " . $model::DEFAULT_SOFT_DELETE
                );
            } else {
                $builder->withoutGlobalScope($this)->whereNull(
                    $model->getQualifiedDeletedAtColumn()
                );
            }
            return $builder;
        });
    }

    /**
     * Add the only-trashed extension to the builder.
     *
     * @param Builder $builder
     * @return void
     */
    protected function addOnlyTrashed(Builder $builder)
    {
        $builder->macro('onlyTrashed', function (Builder $builder) {
            $model = $builder->getModel();

            $reflectionClass = new ReflectionClass($model);
            if ($reflectionClass->hasConstant('DEFAULT_SOFT_DELETE')) {
                $builder->withoutGlobalScope($this)->whereRaw(
                    $model->getQualifiedDeletedAtColumn() . " != " . $model::DEFAULT_SOFT_DELETE
                );
            } else {
                $builder->withoutGlobalScope($this)->whereNotNull(
                    $model->getQualifiedDeletedAtColumn()
                );
            }

            return $builder;
        });
    }
}
