<?php

namespace Climactic\LaravelPolar;

use Climactic\LaravelPolar\Concerns\ManagesBenefits;
use Climactic\LaravelPolar\Concerns\ManagesCheckouts;
use Climactic\LaravelPolar\Concerns\ManagesCustomer;
use Climactic\LaravelPolar\Concerns\ManagesCustomerMeters;
use Climactic\LaravelPolar\Concerns\ManagesOrders;
use Climactic\LaravelPolar\Concerns\ManagesSubscription;

trait Billable // @phpstan-ignore-line trait.unused - Billable is used in the user final code
{
    use ManagesBenefits;
    use ManagesCheckouts;
    use ManagesCustomer;
    use ManagesCustomerMeters;
    use ManagesOrders;
    use ManagesSubscription;
}
