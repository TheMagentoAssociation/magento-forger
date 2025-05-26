<?php

namespace App\DataTransferObjects\Search;

enum FilterType: string
{
    case TERM = 'term';
    case TERMS = 'terms';
    case RANGE = 'range';
}
