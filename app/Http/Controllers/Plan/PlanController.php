<?php

namespace App\Http\Controllers\Plan;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class PlanController extends Controller
{

    use ApiResponser;

    public function index(){

        $plans = Plan::all();

        return $this->showAll($plans);

    }
}
