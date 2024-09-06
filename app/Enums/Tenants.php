<?php

namespace App\Enums;

enum Tenants: string
{
    case MY_COMPANY = 'my-company';
    case TENANT_ONE = 'tenant-one';
    case TENANT_TWO = 'tenant-two';
}
