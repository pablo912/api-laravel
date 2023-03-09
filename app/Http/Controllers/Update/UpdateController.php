<?php

namespace App\Http\Controllers\Update;

use App\Http\Controllers\Controller;
use App\Models\Update;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class UpdateController extends Controller
{

    use ApiResponser;

    public function index(){

        $updates = Update::all();

        return $this->showAll($updates);

    }

}
