<?php
/**
 * Created by Qoliber
 *
 * @category    Qoliber
 * @package     Qoliber_MagentoForger
 * @author      Jakub Winkler <jwinkler@qoliber.com>
 */

declare(strict_types=1);

namespace App\DTO\Search;

enum FilterType: string
{
    case TERM = 'term';
    case TERMS = 'terms';
    case RANGE = 'range';
}
