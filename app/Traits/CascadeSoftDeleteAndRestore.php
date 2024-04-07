<?php
namespace App\Traits;

use Dyrynda\Database\Support\CascadeSoftDeletes;

trait CascadeSoftDeleteAndRestore
{
  use CascadeSoftDeletes;

  /**
   * Boot the trait.
   *
   * Listen for the deleting event of a soft deleting model, and run
   * the delete operation for any configured relationship methods.
   *
   * @throws \LogicException
   */
  protected static function bootCascadeSoftDeleteAndRestore()
  {

    static::restoring(function ($model) {

      $model->validateCascadingSoftDelete();

      $model->runCascadingRestore();
    });
  }

  /**
   * Run the cascading soft delete for this model.
   *
   * @return void
   */
  protected function runCascadingRestore()
  {

    foreach ($this->getActiveCascadingRestores() as $relationship) {
      $this->cascadeSoftRestore($relationship);
    }
  }

  /**
   * Fetch the defined cascading soft deletes for this model.
   *
   * @return array
   */
  protected function getCascadingDeletes()
  {
    return isset($this->cascadeDeletes) ? (array) $this->cascadeDeletes : [];
  }


  /**
   * For the cascading deletes defined on the model, return only those that are not null.
   *
   * @return array
   */
  protected function getActiveCascadingRestores()
  {
    return array_filter($this->getCascadingDeletes(), function ($relationship) {
      return $this->{$relationship}()->withTrashed()->exists();
    });
  }


  /**
   * Cascade delete the given relationship on the given mode.
   *
   * @param  string  $relationship
   * @return void
   */
  protected function cascadeSoftRestore($relationship)
  {
    foreach ($this->{$relationship}()->onlyTrashed()->get() as $model) {
      isset($model->pivot) ? $model->pivot->restore() : $model->restore();
    }
  }
}
