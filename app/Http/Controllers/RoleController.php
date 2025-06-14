<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource for form dropdowns.
     */
    public function index()
    {
        // Any authenticated user might need this list for forms (e.g., admin filtering users)
        $roles = Role::select('id', 'name', 'display_name_ar')->get();
        return response()->json($roles);
    }
}

