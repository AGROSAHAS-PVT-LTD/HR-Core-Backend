<?php

namespace App\Enums;

enum UserAccountStatus: string
{
  case ACTIVE = 'active';

  case INACTIVE = 'inactive';

  case DELETED = 'deleted';

  case ONBOARDING = 'onboarding';

  case RETIRED = 'retired';

  case RELIEVED = 'relieved';

  case SUSPENDED = 'suspended';

  case PENDING = 'pending';

  case REJECTED = 'rejected';

  case APPROVED = 'approved';

  case BLOCKED = 'blocked';

  case INVITED = 'invited';

  case REGISTERED = 'registered';


}
