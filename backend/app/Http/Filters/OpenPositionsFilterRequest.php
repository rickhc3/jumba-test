<?php

namespace App\Http\Filters;

use Illuminate\Http\Request;
use JetBrains\PhpStorm\Pure;

class OpenPositionsFilterRequest extends Filter
{
    /**
     * Set the table to filter
     *
     * @var String
     */
    public $table = "open_positions";

    #[Pure]
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

}
