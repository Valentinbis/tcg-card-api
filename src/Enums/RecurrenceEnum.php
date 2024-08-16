<?php

namespace App\Enums;

enum RecurrenceEnum: string
{
    case Daily = "daily";
    case Weekly = "weekly";
    case Bimonthly = "bimonthly";
    case Quarterly = "quarterly";
    case Monthly = "monthly";
    case Yearly = "yearly";
}
