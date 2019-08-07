<?php

namespace Modules\Extend\Models\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\LU\Models\User as User;
use Modules\Extend\Models\Translation as Post; 

class TranslationPolicy
{
    use HandlesAuthorization;
    
    public function before($user, $ability)
    {
        if (isset($user->perm) && $user->perm->perm_type >= 5) {  //superadmin
            return true;
        }
    }

    /**
     * Determine whether the user can view any DocDummyPluralModel.
     *
     * @param  \Modules\LU\Models\User  $user
     * @return mixed
     */
    public function index(User $user, Post $post)
    {
        return true;
    }

    /**
     * Determine whether the user can view any DocDummyPluralModel.
     *
     * @param  \Modules\LU\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can show the DocDummyModel.
     *
     * @param  \Modules\LU\Models\User  $user
     * @param  \Modules\Extend\Models\Translation  $dummyModel
     * @return mixed
     */
    public function show(User $user, Post $post)
    {
        //
    }

    /**
     * Determine whether the user can view the DocDummyModel.
     *
     * @param  \Modules\LU\Models\User  $user
     * @param  \Modules\Extend\Models\Translation  $dummyModel
     * @return mixed
     */
    public function view(User $user, Post $post)
    {
        //
    }

    /**
     * Determine whether the user can create DocDummyPluralModel.
     *
     * @param  \Modules\LU\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }
    /**
     * Determine whether the user can edit the DocDummyModel.
     *
     * @param  \Modules\LU\Models\User  $user
     * @param  \Modules\Extend\Models\Translation  $dummyModel
     * @return mixed
     */
    public function edit(User $user, Post $post)
    {
        if ($post->created_by == $user->handle) {
            return true;
        }

        return false;
    }

    public function indexEdit(User $user, Post $post)
    {
        if ($post->created_by == $user->handle) {
            return true;
        }

        return false;
    }


    /**
     * Determine whether the user can update the DocDummyModel.
     *
     * @param  \Modules\LU\Models\User  $user
     * @param  \Modules\Extend\Models\Translation  $dummyModel
     * @return mixed
     */
    public function update(User $user, Post $post)
    {
        if ($post->created_by == $user->handle) {
            return true;
        }

        return false;
    }

     public function store(User $user, Post $post)
    {
        /*
        if ($post->created_by == $user->handle) {
            return true;
        }

        return false;
        */
        return true;
    }

    /**
     * Determine whether the user can delete the DocDummyModel.
     *
     * @param  \Modules\LU\Models\User  $user
     * @param  \Modules\Extend\Models\Translation  $dummyModel
     * @return mixed
     */
    public function delete(User $user, Post $post)
    {
        if ($post->created_by == $user->handle) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the DocDummyModel.
     *
     * @param  \Modules\LU\Models\User  $user
     * @param  \Modules\Extend\Models\Translation  $dummyModel
     * @return mixed
     */
    public function restore(User $user, Post $post)
    {
        if ($post->created_by == $user->handle) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the DocDummyModel.
     *
     * @param  \Modules\LU\Models\User  $user
     * @param  \Modules\Extend\Models\Translation  $dummyModel
     * @return mixed
     */
    public function forceDelete(User $user, Post $post)
    {
        //
    }
}
