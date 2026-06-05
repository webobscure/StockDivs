<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ticker', 'company_name', 'exchange', 'country', 'currency', 'description', 'provider'])]
class Stock extends Model {}
